# STAGE 1: Build tahap instalasi dan kompilasi Vue.js
FROM node:20-alpine AS builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# STAGE 2: Tahap Production menggunakan Nginx (lebih ringan dan cepat)
FROM nginx:alpine
# Menyalin hasil build dari STAGE 1 ke dalam server Nginx
COPY --from=builder /app/dist /usr/share/nginx/html
# (Opsional) Mengcopy konfigurasi nginx jika routing Vue (history mode) bermasalah
# COPY nginx.conf /etc/nginx/conf.d/default.conf
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]