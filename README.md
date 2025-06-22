
# Specifi Profit Optimiser – README

## Overview

This project is a working prototype of the Specifi Profit Optimiser. The tool helps AV dealers and businesses assess quote profitability and receive actionable AI-driven suggestions for improvement.

---

## Stack & Versions

- **Framework:** Laravel 10.x (Backend), Inertia.js + Vue 3 (Frontend)
- **Styling:** TailwindCSS
- **PDF Export:** jsPDF & html2canvas
- **AI Integration:** OpenAI GPT-4 (Only OpenAI implemented, others disabled)
- **Database:** MySQL (or SQLite, for local setup)
- **Other:** Axios (API calls), Inertia Progress (loading/progress bar)

---

## Installation

1. **Clone the repository**

    ```bash
    git clone git@github.com:nidheesh1994/specifi-profit-optimiser.git
    cd specifi-profit-optimiser
    ```

2. **Install backend dependencies**

    ```bash
    composer install
    cp .env.example .env
    php artisan key:generate
    # Edit your .env for DB config
    php artisan migrate --seed
    ```

3. **Install frontend dependencies**

    ```bash
    npm install
    npm run dev
    ```

4. **Run the application**

    ```bash
    php artisan serve
    ```

5. **Access the app**

    Go to [http://localhost:8000](http://localhost:8000) in your browser.

4. **Login info**

    ```bash
    username: admin@specifi.test
    password: password123
    ```

---

## Functionality

- **Settings:** Save your OpenAI key, select model, and check API connection status.
- **Input Form:** Enter products/services (cost, sell price, quantity), labor hours, labor cost/hour, overheads, and target margin.
- **Quote Calculations:**
    - Calculates line item margins, total gross profit, net profit after labor & overheads, overall margin.
    - Flags low-margin products (<10% red, <20% yellow).
    - Health indicator (green/amber/red) based on margin thresholds.
- **AI Suggestions:** Calls OpenAI API (gpt-4) to provide improvements on margin, labor, product swaps, and a client-friendly summary. (Other LLM providers are disabled in this version.)
- **Quote Editing:** Edit products, constraints, and customer details via modals.
- **Export to PDF:** Preview and export the quote (with all calculations and AI feedback) as a nicely formatted PDF.

---

## Assumptions & Approach

- **Calculations:**
    - **Margin** = ((Sell Price - Cost) / Sell Price) * 100
    - **Gross Profit** = Sum over all products [(Sell Price - Cost) * Quantity]
    - **Labor Cost** = Labor Hours × Labor Cost per Hour
    - **Net Profit** = Gross Profit - Labor Cost - Fixed Overheads
    - **Calculated Margin** = (Net Profit / (Total Sell Price)) * 100
- **Low Margin Warnings:** Items with margin <10% are red, <20% are yellow.
- **Health Status:** Based on calculated margin (configurable thresholds, e.g., <10% red, <20% amber, else green).
- **AI Integration:** Only OpenAI is enabled; API key is required per user. Hugging Face/self-hosted can be enabled in future versions.
- **UX:** Modals are used for a clean, single-page experience; progress bar appears on AI or connection actions.
- **Export:** The Entire quote preview is exported as a PDF, including AI feedback. AI feedback can be hidden if needed

---

## How It Works (User Flow)

1. **Add/Edit Quote:** Select products, enter labor/financials, and save.
2. **Get AI Suggestions:** Click "Generate AI Suggestion" to fetch recommendations from OpenAI.
3. **Edit/Review:** You can adjust details as needed; suggestions can be regenerated.
4. **Export:** Click "Export Quote" for a preview and export to PDF.
5. **Settings:** Go to settings to configure your OpenAI key and test the connection.

---

## Limitations & Future Improvements

- Only OpenAI is currently supported for AI suggestions.
- Hugging Face/self-hosted LLMs are planned but disabled.

---

## Author & Contact

Built by Nidheesh Jagadeesan.

If you have any questions, please reach out via email or GitHub.

---
