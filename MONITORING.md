# Monitoring Prometheus + Grafana (Pertemuan 11)

Stack monitoring untuk aplikasi Hunian Pegawai: Prometheus, Grafana, cAdvisor, node-exporter, Redis (penyimpanan metrik aplikasi), dan exporter kecil pembaca statistik Docker.

## Menjalankan

1. Salin `.env.example` menjadi `.env`, lalu isi `GRAFANA_PASS` dengan password kuat.
2. Dari root repo:

```bash
docker compose -f docker-compose.yml -f compose.monitoring.yaml up -d --build
docker compose -f docker-compose.yml -f compose.monitoring.yaml exec -T backend php artisan migrate --seed --force
```

3. Tunggu minimal 30 detik, lalu buka:

- Prometheus targets: <http://localhost:9090/targets>
- Prometheus alerts: <http://localhost:9090/alerts>
- Grafana: <http://localhost:3000> (dashboard `Monitoring Aplikasi Hunian Pegawai`)
- Metrik aplikasi: <http://localhost:8000/metrics>

Port 9090 dan 3000 hanya terikat ke `127.0.0.1`. cAdvisor, node-exporter, Redis, dan exporter tidak dipublikasikan ke host.

## Mengisi `.env`

`.env` berada di root repo (folder compose), bukan `backend/.env`.

```dotenv
GRAFANA_PASS=ganti-dengan-password-kuat
```

Jangan commit `.env`. `.env.example` hanya berisi placeholder.

## Simulasi alarm

```bash
docker compose -f docker-compose.yml -f compose.monitoring.yaml stop backend
```

Pantau <http://localhost:9090/alerts>. Aturan `LayananMati` (`up{job="aplikasi"} == 0`, `for: 1m`) berubah dari `pending` menjadi `firing` setelah kurang lebih satu menit. Setelah itu:

```bash
docker compose -f docker-compose.yml -f compose.monitoring.yaml start backend
```

Tunggu satu siklus evaluasi sampai target kembali `UP` dan alarm kembali `inactive`.

## Troubleshooting

- **Panel "No data"**: buka `/targets` lebih dulu. Pastikan job `aplikasi` dan `container` berstatus `UP`, lalu periksa `/metrics` pada backend.
- **Grafik terus naik**: metrik counter seperti `http_requests_total` harus dibungkus `rate(...[5m])` atau `increase(...[5m])`, jangan ditampilkan mentah.
- **Label container kosong**: pada Docker Desktop dengan containerd image store, cAdvisor v0.49.1 gagal membaca `layerdb` sehingga hanya menghasilkan metrik agregat. Stack ini menjalankan cAdvisor dengan `cgroup: host` dan menambahkan `docker-exporter` yang membaca Docker API melalui `/var/run/docker.sock` untuk mengekspos `container_cpu_usage_seconds_total` dan `container_memory_usage_bytes` dengan label `name`.
- **node-exporter gagal start**: mount `rslave` tidak didukung Docker Desktop; gunakan `/:/host:ro` seperti pada `compose.monitoring.yaml`.
- **Grafana menolak password dari `.env`**: password hanya dipakai saat volume `grafana-data` pertama kali dibuat. Reset dengan `docker compose -f docker-compose.yml -f compose.monitoring.yaml exec grafana grafana cli admin reset-admin-password "$GRAFANA_PASS"`.

## Keamanan

- Password admin Grafana berasal dari `.env`, bukan `admin/admin`.
- Pendaftaran pengguna mandiri dimatikan (`GF_USERS_ALLOW_SIGN_UP=false`).
- Hanya Prometheus dan Grafana yang dapat diakses dari host, itu pun lewat loopback.
- Token dan password tidak ditulis ke file yang di-commit.
