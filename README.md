# 🚢 CargoTrace — Global Export Logistics Visibility Platform

> **PT Mayora Indah Tbk Export Supply Chain & Cargo Visibility Platform**  
> Built with Laravel 12, Tailwind CSS, Leaflet.js OpenStreetMap, Chart.js, and Real-Time Multi-Currency & Telemetry Streaming.

---

## 🌟 Overview

**CargoTrace** is an enterprise-grade shipment visibility and supply chain management web application tailored for **PT Mayora Indah Tbk**'s international export operations. The platform monitors multi-modal cargo movements (Sea, Air, Land) across 100+ export destination countries, providing real-time tracking, foreign exchange volatility risk monitoring, multilingual support, and telemetry feeds.

---

## 🚀 Key Features

### 1. 🗺️ Multi-Modal Live Tracking & Visual Maps
- **Uniform Multi-Modal Animation**: Synchronized pulse radar waves (2.4s rhythm) and custom SVGs for **Laut (Kapal)**, **Udara (Pesawat)**, and **Darat (Mobil Truk)**.
- **Interactive OpenStreetMap (Leaflet.js)**: Real GPS coordinates and UN/LOCODE ports (Tanjung Priok, Port of Rotterdam, Port of Los Angeles, Manila, etc.).
- **Dynamic Waypoints & Status Callouts**: Live route polylines, nautical markers, weather integration (Open-Meteo API), and vessel telemetry.
- **Deterministic ETA & Delay Engine**: Categorizes delays into `ON_TIME`, `LOW_DELAY`, `MEDIUM_DELAY`, and `HIGH_DELAY` with automated delay reasons.

### 2. 💱 100% Real-Time Multi-Currency Monitoring (Zero Hardcoding)
- **Live External API Data**: Fetches genuine real-time FX rates from Open Exchange Rates (`open.er-api.com`) with redundant fallback to `api.exchangerate-api.com` and CDN endpoints.
- **Global Currency Agnostic**: Supports 14 major currencies (`USD`, `IDR`, `EUR`, `SGD`, `CNY`, `SAR`, `AED`, `PHP`, `MYR`, `JPY`, `GBP`, `AUD`, `THB`, `VND`).
- **Mode 1-to-1 (Single Target Focus)**:
  - Nominal input, quick chips (1, 100, 1.000, 10.000), Mayora cargo presets (Kopiko FCL, Danisa FCL, Air Express, Truk Tronton).
  - Instant swap button (⇄), live exchange rate, and inverse rate (`1 IDR = ... USD`).
- **Mode 1-to-Many (Global Grid)**:
  - Multi-target currency cards with regional filter chips (`Semua`, `🌏 ASEAN`, `🇪🇺 Eropa`, `🕌 Timteng`, `🇨🇳 Asia Timur`).
  - "Fokus ➔" shortcut to immediately target any currency in 1-to-1 mode.
- **Change Detection Alert Engine**:
  - Compares live rates against the previous snapshot in `localStorage`.
  - Configurable threshold (`≥ 0.10%`, `≥ 0.25%`, `≥ 0.50%`, `≥ 1.00%`).
  - Prominent alert banner displaying triggered currency pairs, direction (`▲` / `▼`), delta values, and percentages.
- **Simulated Test Mode**:
  - Dedicated simulation buttons (`+1.5% Surge`, `-1.2% Drop`) with high-visibility warning notices cleanly separated from real API feeds, plus a 1-click reset to live API data.
- **Chart.js FX Visualizer**: Historical trend chart with 7-day, 14-day, and 30-day selector and high/low/current statistics.
- **Central Bank Interest Rates**: Benchmarks for Bank Indonesia (BI), Federal Reserve (Fed), European Central Bank (ECB), Bank of England (BoE), PBOC, Bank of Japan (BOJ), and SOFR.

### 3. 🌐 Multilingual Support (7 World Languages)
Seamless instant language switcher in the header:
- 🇮🇩 **Bahasa Indonesia (ID)**
- 🇬🇧 **English (EN)**
- 🇨🇳 **Mandarin / 简体中文 (ZH)**
- 🇸🇦 **Arabic / العربية (AR)**
- 🇯🇵 **Japanese / 日本語 (JA)**
- 🇪🇸 **Spanish / Español (ES)**
- 🇫🇷 **French / Français (FR)**

### 4. ⚡ Real-Time Telemetry Running Ticker
- Top marquee ticker streaming live port temperatures, weather conditions, central bank rate alerts, and maritime logistics news headlines.

### 5. 👥 Role-Based Access Control (RBAC)
- **Administrator**: Complete operational oversight, carrier directories, user management, and system logs.
- **Logistics Operator**: Route management, event timeline updates, and exception resolution.
- **Global Buyer / Customer**: Restricted access to view only their own assigned consignments.

---

## 🛠️ Technology Stack

| Layer | Technologies |
|---|---|
| **Backend** | PHP 8.3+, Laravel 12, MySQL 8.0, Laravel Sanctum |
| **Frontend** | Blade Templates, Tailwind CSS, Vanilla JavaScript (ES6+), FontAwesome / SVG Icons |
| **Maps & Charts** | Leaflet.js, OpenStreetMap, Chart.js (CDN) |
| **APIs** | Open Exchange Rates API, Open-Meteo Marine Forecast API, World Time API |
| **Testing** | PHPUnit 12 (100% Passing Feature Tests with `DatabaseTransactions`) |

---

## 📦 Installation & Setup Guide

### 1. Prerequisites
- [Laragon](https://laragon.org/) (or Apache/Nginx + MySQL 8.0 + PHP 8.2+)
- Composer 2+
- Git

### 2. Clone & Setup Environment
```bash
git clone https://github.com/Wennyfadh/Projectcargo.git
cd Projectcargo

# Copy environment template
cp .env.example .env
```

### 3. Configure Database
Ensure MySQL is running, then create database `cargotrace`:
```sql
CREATE DATABASE cargotrace CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Update your `.env` configuration:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cargotrace
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Install Dependencies & Generate Key
```bash
composer install
php artisan key:generate
```

### 5. Run Migrations & Comprehensive Seeders
```bash
php artisan migrate:fresh --seed
```

### 6. Start Development Server
```bash
php artisan serve --port=8000
```
Visit `http://127.0.0.1:8000` in your web browser.

---

## 🔑 Demo Login Accounts

All pre-seeded demo accounts are configured with the password: `password`

| Role | Email | Password | Scope |
|---|---|---|---|
| **System Admin** | `admin@admin.com` | `password` | Full Global Access |
| **CargoTrace Admin** | `admin@cargotrace.com` | `password` | Full Global Access |
| **Mayora Logistics Director** | `admin@cargotrace.mayora.co.id` | `password` | Internal Supply Chain |
| **Logistics Operator** | `operator@cargotrace.mayora.co.id` | `password` | Port & Fleet Dispatch |
| **Global Buyer (USA)** | `buyer.usa@asianfoodgrocer.com` | `password` | US Shipments Only |
| **Global Buyer (Europe)** | `buyer.europe@orientalgroup.nl` | `password` | European Shipments Only |
| **Global Buyer (Middle East)** | `buyer.middleeast@lulugroup.ae` | `password` | Gulf Region Shipments Only |

---

## 🧪 Automated Testing

CargoTrace includes a comprehensive automated test suite covering authentication, RBAC authorization, ETA calculations, multi-modal map rendering, and live currency conversions:

```bash
php artisan test
```

Test results summary:
```text
Pass: 23 passed (187 assertions)
```

---

## 📄 License & Attribution

Developed for **PT Mayora Indah Tbk Global Logistics Visibility Platform** (UAS Web Programming 2).
All product references (Kopiko, Danisa, Torabika, Beng-Beng) are registered trademarks of PT Mayora Indah Tbk.
