# 📈 Companies, Stocks & Trading Game Economy API

This is a **Game Economy Backend** built with **Vanilla PHP** (no framework) and **MySQL**, designed to simulate a real-time stock market for a scouting game or simulation.

## 🌟 Overview

The application simulates a dynamic economy where:
* **Companies** (Teams/Groups) have **Cash** and own **Stocks**.
* **Stocks** are shares of companies. The price of a stock is dynamic, calculated based on the target company's **Net Worth**.
    * **Net Worth** = `Cash + (Portfolio Value)`.
* **Trading:** Companies can buy/sell stocks from each other or the Bank. Prices fluctuate instantly as companies earn cash or trade.
* **Tasks:** Companies complete tasks (e.g., "Knot Tying", "Trivia") to earn cash rewards. The system uses a **Tiered Reward System** (1st place gets P1 reward, 2nd gets P2, ..., 4th and 5th get penalties).
* **History:** The system acts as a "Gatekeeper" to record minute-by-minute snapshots of company values for historical graphing.

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
DB_TYPE=mysql
DB_SERVER=mysql
DB_NAME=developmentdb
DB_USER=developers
DB_PASS=secret123
```

### 3. Start the Application

Run the application using Docker Compose. This spins up the **PHP-Apache** container and the **MySQL** database.

```bash
docker-compose up -d --build

```

### 4. Database Initialization

The database will automatically initialize using the script in `sql/setup.sql` on the first run.

* **Default Admin User:** `StockMaster` / `password123`
* **Default Companies:** Haviken, Spechten, Sperwers, Zwaluwen, Valken

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

| Method | Endpoint              | Description                                           |
|--------|-----------------------|-------------------------------------------------------|
| `GET`  | `/api/companies`      | List all companies with live Net Worth & Stock Price. |
| `GET`  | `/api/companies/{id}` | Get details for a specific company.                   |
| `POST` | `/api/history/save`   | Trigger a valuation snapshot (Heartbeat).             |
| `GET`  | `/api/history/{date}` | Get valuation history since `{date}`.                 |

### ✅ Tasks

| Method | Endpoint              | Description                                                                    |
|--------|-----------------------|--------------------------------------------------------------------------------|
| `GET`  | `/api/tasks`          | List all tasks with their category, rewards, and completion status.            |
| `POST` | `/api/tasks/complete` | Mark a task as completed for a company. Automatically assigns rank and reward. |

**Payload:** `{"company_id": 1, "task_id": 5}`

### 📈 Stocks & Trading

| Method | Endpoint                   | Description                                        |
|--------|----------------------------|----------------------------------------------------|
| `GET`  | `/api/stocks`              | View all active shares owned by companies.         |
| `GET`  | `/api/stocks/bank`         | View shares owned by the Bank (available for IPO). |
| `GET`  | `/api/stocks/company/{id}` | View a specific company's portfolio.               |
| `POST` | `/api/stocks/trade`        | Buy/Sell stocks.                                   |

**Payload:** `{"buyer_id": 1, "seller_id": null, "stock_company_id": 2, "amount": 10}`
*(Use `seller_id: null` to buy from the Bank)*

### 💸 Transactions

| Method | Endpoint            | Description                                  |
|--------|---------------------|----------------------------------------------|
| `GET`  | `/api/transactions` | View global transaction history (cash flow). |
| `POST` | `/api/transactions` | Create a manual transaction (Admin only).    |

---

## 🛠 Tech Stack

* **Language:** PHP 8.2 (Vanilla)
* **Database:** MySQL 8.0
* **Router:** `bramus/router`
* **Auth:** JWT (`firebase/php-jwt`)
* **Docs:** Swagger / OpenAPI 3.0
