const http = require('http');

const DOCKER_SOCKET = '/var/run/docker.sock';
const PORT = 9101;
const CACHE_MS = 4000;

let cache = { at: 0, body: '' };

function dockerGet(path) {
  return new Promise((resolve, reject) => {
    const request = http.request(
      { socketPath: DOCKER_SOCKET, path, method: 'GET' },
      (response) => {
        let data = '';
        response.setEncoding('utf8');
        response.on('data', (chunk) => {
          data += chunk;
        });
        response.on('end', () => {
          if (response.statusCode >= 200 && response.statusCode < 300) {
            try {
              resolve(JSON.parse(data));
            } catch (error) {
              reject(error);
            }
          } else {
            reject(new Error(`Docker API ${path} returned ${response.statusCode}`));
          }
        });
      }
    );
    request.on('error', reject);
    request.setTimeout(10000, () => request.destroy(new Error('Docker API timeout')));
    request.end();
  });
}

function escapeLabel(value) {
  return String(value).replace(/\\/g, '\\\\').replace(/"/g, '\\"').replace(/\n/g, '\\n');
}

async function collect() {
  const containers = await dockerGet('/containers/json');
  const lines = [];
  const seenAt = Math.floor(Date.now() / 1000);

  const stats = await Promise.all(
    containers.map(async (container) => {
      try {
        const data = await dockerGet(`/containers/${container.Id}/stats?stream=false`);
        return { container, data };
      } catch (error) {
        return { container, data: null };
      }
    })
  );

  const cpuLines = [];
  const memoryLines = [];
  const seenLines = [];

  for (const { container, data } of stats) {
    const name = (container.Names && container.Names[0] ? container.Names[0] : container.Id).replace(/^\//, '');
    const labels = `name="${escapeLabel(name)}",id="${escapeLabel(container.Id.substring(0, 12))}"`;

    seenLines.push(`container_last_seen{${labels}} ${seenAt}`);

    if (!data || !data.cpu_stats || !data.cpu_stats.cpu_usage) {
      continue;
    }

    const totalUsage = Number(data.cpu_stats.cpu_usage.total_usage || 0);
    cpuLines.push(`container_cpu_usage_seconds_total{cpu="total",${labels}} ${(totalUsage / 1e9).toFixed(6)}`);

    if (data.memory_stats && typeof data.memory_stats.usage === 'number') {
      memoryLines.push(`container_memory_usage_bytes{${labels}} ${data.memory_stats.usage}`);
    }
  }

  lines.push('# HELP container_cpu_usage_seconds_total Cumulative CPU time consumed by the container in seconds.');
  lines.push('# TYPE container_cpu_usage_seconds_total counter');
  lines.push(...cpuLines);
  lines.push('# HELP container_memory_usage_bytes Current memory usage of the container in bytes.');
  lines.push('# TYPE container_memory_usage_bytes gauge');
  lines.push(...memoryLines);
  lines.push('# HELP container_last_seen Unix timestamp when the container was last observed by the exporter.');
  lines.push('# TYPE container_last_seen gauge');
  lines.push(...seenLines);

  return lines.join('\n') + '\n';
}

async function metrics() {
  const now = Date.now();
  if (now - cache.at < CACHE_MS) {
    return cache.body;
  }

  try {
    const body = await collect();
    cache = { at: now, body };
    return body;
  } catch (error) {
    return `# HELP container_exporter_error Exporter could not read Docker statistics.\n# TYPE container_exporter_error gauge\ncontainer_exporter_error 1\n`;
  }
}

http
  .createServer(async (request, response) => {
    if (request.url === '/health') {
      response.writeHead(200, { 'Content-Type': 'text/plain' });
      response.end('ok\n');
      return;
    }

    if (request.url === '/metrics') {
      const body = await metrics();
      response.writeHead(200, { 'Content-Type': 'text/plain; version=0.0.4' });
      response.end(body);
      return;
    }

    response.writeHead(404, { 'Content-Type': 'text/plain' });
    response.end('not found\n');
  })
  .listen(PORT, '0.0.0.0');
