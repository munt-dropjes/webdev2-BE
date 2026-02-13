# 📈 Companies, Stocks & Trading Game Economy API

This is a **Game Economy Backend** built with **Vanilla PHP** (no framework) and **MySQL**, designed to simulate a real-time stock market for a scouting game or simulation.

## 🌟 Overview

The application simulates a dynamic economy with high-stakes mechanics:

* **Companies:** (Teams/Groups) have **Cash** and own **Stocks**.
    * **Privacy:** A company's Cash balance is **private**. Only Admins and the company owner can see it. Other players only see Net Worth.
* **Stock Valuation:**
    * **Stocks** are shares of companies. The price of a stock is dynamic, calculated based on the target company's **Net Worth**.
    * **Net Worth** = `Cash + (Portfolio Value)`.
    * **Stock Price** = `(Net Worth / 100)`.
    * *Buying stocks increases your Net Worth, but decreases your Cash (and therefore your own Stock Price), creating a strategic trade-off.*
* **One-Shot Task System:**
    * Companies complete tasks to earn cash.
    * **High Stakes:** A company has **only one attempt** per task.
    * **Success:** Earns the tiered reward (1st place gets P1, 2nd gets P2, etc.).
    * **Failure:** The company pays a **Penalty** (deducted from cash) and is **blocked** from retrying.
    * **Fairness:** Failed attempts do *not* consume a Rank slot. If Company A fails, Company B can still claim the 1st Place reward.
* **History:** The system records minute-by-minute snapshots of company values for historical graphing.

---

## 🚀 Installation & Setup

### Prerequisites
* [Docker](https://www.docker.com/) and Docker Compose installed.

### 1. Clone & Configure
```bash
git clone <your-repo-url>
cd <your-repo-folder>

# Copy the example environment file
cp .env.example .env

```

### 2. Configure Environment (.env)

Open `.env` and set your database credentials. If using the provided `docker-compose.yml`, these defaults work out of the box:

```ini
# Database Configuration
DB_TYPE=mysql
DB_SERVER=mysql
DB_USER=developer
DB_PASS=secret123
DB_NAME=developmentdb
DB_PORT=8080

# JWT Configuration
JWT_SECRET=default_secret_for_dev
JWT_ALGO=HS256
JWT_ISSUER=http://localhost/api
JWT_EXPIRE_TIME=3600
```

### 3. Start the Application

Run the application using Docker Compose. This spins up the **PHP-Apache** container and the **MySQL** database.

```bash
docker-compose up -d --build
```

### 4. Database Initialization

The database will automatically initialize using the script in `sql/setup.sql` on the first run.

* **Default Admin User:** `StockMaster`
* **Default Companies:** `Haviken`, `Spechten`, `Sperwers`, `Zwaluwen` & `Valken`
* **Default Password for all users:** `password123`

---

## 📡 Usage

The API is accessible at `http://localhost`.

### Authentication

Most endpoints are **protected** and require a Bearer Token.

1. **Login** via `POST /login` with `{"username": "StockMaster", "password": "password123"}`.
2. Copy the `token` from the response.
3. Add it to your request headers:
   `Authorization: Bearer <your_token_here>`

### Heartbeat / Snapshot Trigger

To generate historical data for graphs, the backend expects a "pulse". You should call this endpoint every minute (via cron job or frontend loop).

* **Endpoint:** `POST /api/history/save`
* **Logic:** The backend checks if a snapshot exists for the current minute. If not, it saves one. If yes, it skips.

---

## 📚 API Endpoints

### 🔐 Auth & System

| Method | Endpoint       | Description                         |
|--------|----------------|-------------------------------------|
| `POST` | `/login`       | Authenticate and receive JWT token. |
| `GET`  | `/ping`        | Public health check.                |
| `GET`  | `/diagnostics` | System diagnostics.                 |

### 🏢 Companies & History

| Method | Endpoint              | Description                                                                                           |
|--------|-----------------------|-------------------------------------------------------------------------------------------------------|
| `GET`  | `/api/companies`      | List all companies with live Net Worth & Stock Price. Cash is hidden for non-owners. Prices are live. |
| `GET`  | `/api/companies/{id}` | Get details for a specific company.                                                                   |
| `POST` | `/api/history/save`   | Trigger a valuation snapshot (Heartbeat).                                                             |
| `GET`  | `/api/history/{date}` | Get valuation history since `{date}`. Date must be URL Encoded Y-m-d H:i:s.                           |

### ✅ Tasks

| Method | Endpoint              | Description                                                                                                                      |
|--------|-----------------------|----------------------------------------------------------------------------------------------------------------------------------|
| `GET`  | `/api/tasks`          | List all tasks with their category, rewards, and completion status. Response includes finished_by (winners) and failed (losers). |
| `POST` | `/api/tasks/complete` | Submit a task attempt. Irreversible. Success automatically assigns rank and reward.; Failure yields penalty + block.             |

Payload (Success):
```JSON
{
"company_id": 1,
"task_id": 5,
"success": true
}
```
Payload (Failure/Penalty):
```JSON
{
"company_id": 1,
"task_id": 5,
"success": false
}
```

### 📈 Stocks & Trading

| Method | Endpoint                   | Description                                                |
|--------|----------------------------|------------------------------------------------------------|
| `GET`  | `/api/stocks`              | View all active shares owned by companies.                 |
| `GET`  | `/api/stocks/bank`         | View shares owned by the Bank.                             |
| `GET`  | `/api/stocks/company/{id}` | View a specific company's portfolio.                       |
| `POST` | `/api/stocks/trade`        | Buy/Sell stocks. Checks for sufficient Cash & Stock funds. |

**Payload:** 
```JSON
{
  "buyer_id": 1,
  "seller_id": null,
  "stock_company_id": 2,
  "amount": 10
}
```
*(Use `seller_id: null` to buy from the Bank)*

### 💸 Transactions

| Method | Endpoint                     | Description                                                    |
|--------|------------------------------|----------------------------------------------------------------|
| `GET`  | `/api/transactions`          | View transaction history. Admins see all; Users see their own. |
| `POST` | `/api/transactions`          | Create a manual transaction (Admin only).                      |
| `POST` | `/api/transactions/transfer` | Transfer money from one company to another company.            |

**Payload:**
```JSON
{
  "sender_id": 1,
  "receiver_id": 2,
  "amount": 1000,
  "description": "Secret deal transfer"
}
```
---

## 🛠 Tech Stack

* **Language:** PHP 8.2 (Vanilla)
* **Database:** MySQL 8.0
* **Router:** `bramus/router`
* **Auth:** JWT (`firebase/php-jwt`)
* **Docs:** Swagger / OpenAPI 3.0
