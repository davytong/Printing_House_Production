# Telegram Message Testing Policy & Group Restrictions

## CRITICAL SAFETY DIRECTIVE: ZERO NOISE TO STAFF WORK GROUPS

When testing, developing, debugging, executing test runs, or sending verification messages via the Telegram Bot, **NEVER send test messages to the live staff work groups**. Sending test messages to production groups disturbs staff members and causes operational confusion.

---

### 1. Telegram Group Classification

#### 🚫 PRODUCTION / LIVE WORK GROUPS (STRICTLY FORBIDDEN FOR TESTS)
DO NOT send any test messages, simulated reports, trial alerts, or debug notifications to these groups:
- **BELTEI Printing Press**:
  - Group Chat ID: `-4646583053`
  - *Active staff are in this group.*
- **Press Processing Works**:
  - Supergroup Chat ID: `-1003150870760`
  - All Forum Topics (Paper Report `#881`, Finishing Report `#882`, Stock Alert `#885`, Consumable Stock `#2007`, Stock Usage `#914`).
  - *Active production management channels.*

#### ✅ DESIGNATED TESTING GROUPS (USE ONLY THESE FOR TESTS)
ALL test messages, trial reports, test alerts, automated test verifications, and manual trigger demonstrations MUST ONLY target:
- **Testing Supergroup**:
  - Chat ID: `-1003744799209` (Primary testing destination)
- **Testing Group**:
  - Chat ID: `-5150858234` (Secondary testing destination)

---

### 2. Operational & Coding Rules

1. **Automated Tests**:
   - Automated tests (`php artisan test`) must always mock HTTP requests (`Http::fake()`) or prevent external HTTP calls so no real messages are ever dispatched over the network during test runs.

2. **Manual & CLI Testing**:
   - When manually verifying report alerts, daily summaries, or bot messages via Tinker, CLI commands (`reports:check-deadlines`, `reports:daily-summary`), or admin web buttons, always ensure the target chat ID is explicitly set to the Testing Group (`-1003744799209`).

3. **Fallback Destination Safeguard**:
   - If `Setting::get('report_alert_chat_id')` is not set or in test mode, the system must default to `-1003744799209` (Testing), NEVER `-4646583053` (Work Group).

4. **Code-Level Production Guard**:
   - Any message containing test keywords (e.g., `[TEST]`, `[DEBUG]`, `Test Mode`, `🧪`) must be strictly blocked by `TelegramService` from sending to production group IDs `-4646583053` and `-1003150870760`.
