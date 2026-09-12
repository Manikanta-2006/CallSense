# 📞 CallSense — AI-Powered Call Sentiment & Risk Analysis Platform

<p align="center">
  <a href="https://callsense-s62f.onrender.com"><img src="https://img.shields.io/badge/Live_Demo-https%3A%2F%2Fcallsense--s62f.onrender.com-46E399?style=for-the-badge&logo=render&logoColor=white" alt="Live Demo"></a>
  <img src="https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 13">
  <img src="https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.3+">
  <img src="https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/Vite-6.x-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite">
  <img src="https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge" alt="License">
</p>

> 🌐 **Live Application**: [https://callsense-s62f.onrender.com](https://callsense-s62f.onrender.com)

**CallSense** is an intelligent, real-time audio transcript analysis and risk monitoring system designed for customer support teams, call centers, and emergency services. It leverages AI-driven Speech-to-Text (STT) and Natural Language Processing (NLP) to transcribe audio recordings, detect caller emotions, calculate risk scores, and automatically generate high-priority operational alerts.

---

## 🌟 Key Features

### 🎙️ 1. Speech-to-Text & Audio Transcription
- **Multi-Format Support**: Upload audio call recordings in `MP3`, `WAV`, `OGG`, `M4A`, `WEBM`, or `OPUS` formats (up to 20MB).
- **Automated Transcription**: Seamless conversion of speech to text via integrated AI transcription pipeline.
- **Custom Transcript Input**: Manual transcript entry fallback for quick testing and analysis.

### 🧠 2. AI Sentiment & Emotion Analysis
- **Sentiment Scoring**: Calculates sentiment scores ranging from `-1.0` (Highly Negative) to `+1.0` (Highly Positive).
- **Emotion Classification**: Categorizes caller tone into distinct emotional states (e.g., *Frustrated, Angry, Neutral, Satisfied, Delighted*).
- **Sentence-Level Breakdown**: Detailed line-by-line sentiment analysis of conversations to pinpoint escalation moments.
- **Keyword & Entity Extraction**: Identifies critical keywords (e.g., *refund, lawsuit, supervisor, urgent, cancel service*).

### ⚠️ 3. Risk Scoring & Automated Escalation
- **Risk Score Algorithm**: Computes real-time risk scores (0–100%) based on negative sentiment intensity and detected high-risk keywords.
- **Auto-Generated Alerts**: Automatically triggers `High` or `Critical` severity alerts when risk thresholds or negative scores are exceeded.
- **Repeat Complaint Detection**: Identifies recurring issues across multiple customer interactions to highlight systemic support problems.
- **Alert Workflow Management**: Update alert status (`pending`, `reviewed`, `resolved`) and escalate high-priority issues directly from the dashboard.

### 📊 4. Real-Time Dashboard & Analytics
- **Live Analytics**: View call volumes, average sentiment score, emotion distributions, and pending alert counts.
- **Call History & Filtering**: Search and filter past analyzed calls by agent, customer, risk score, or emotion.
- **CSV Data Export**: One-click CSV export of analyzed call logs for offline reporting and auditing.

### 🔐 5. Security & Authentication
- **Multi-Auth System**: Supports standard email/password authentication alongside **Google OAuth 2.0 (Socialite)** single sign-on.
- **Session & Audit Tracking**: Tracks user login activities, IP addresses, user agents, and logout events.

---

## 🏗️ Architecture & Technology Stack

- **Backend Framework**: [Laravel 13](https://laravel.com/) (PHP 8.3+)
- **Frontend / Styling**: Blade Components, Tailwind CSS, Vite, JavaScript
- **Database**: SQLite (default) / MySQL / PostgreSQL
- **Authentication**: Laravel Session Auth & Laravel Socialite (Google OAuth 2.0)
- **Background Jobs & Logging**: Laravel Queue & Laravel Pail

---

## 🚀 Getting Started

### Prerequisites
Make sure you have the following installed on your system:
- **PHP** `>= 8.3`
- **Composer** `>= 2.0`
- **Node.js** `>= 18.0` & **npm**

### Installation

1. **Clone the Repository**
   ```bash
   git clone https://github.com/Manikanta-2006/CallSense.git
   cd CallSense
   ```

2. **Install PHP & Node Dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Configure Environment File**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Set Up Database**
   ```bash
   touch database/database.sqlite
   php artisan migrate --seed
   ```

5. **Start Development Server**
   Run the unified development command:
   ```bash
   npm run dev
   ```
   *This command concurrently starts the Laravel server, queue listener, log viewer, and Vite dev server.*

6. **Access the Application**
   Open your browser and navigate to `http://localhost:8000`.

---

## 🛠️ Configuration (.env)

Configure your API keys and credentials in the `.env` file:

```env
APP_NAME=CallSense
APP_ENV=local
APP_URL=http://localhost:8000

# Google OAuth Credentials (Optional)
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"

# Speech-to-Text & NLP API Keys (Optional)
SPEECH_TO_TEXT_API_KEY=your-stt-api-key
SENTIMENT_API_KEY=your-sentiment-api-key
```

---

## 📂 Project Structure

```
CallSense/
├── app/
│   ├── Http/Controllers/
│   │   ├── AlertController.php      # Alert status & escalation management
│   │   ├── AuthController.php       # Login, Register & Google OAuth
│   │   ├── CallController.php       # Call upload & AI sentiment pipeline
│   │   ├── DashboardController.php  # Analytics & metrics
│   │   └── ProfileController.php    # User profile management
│   ├── Models/
│   │   ├── Alert.php                # Operational alerts model
│   │   ├── Call.php                 # Call recording & analysis model
│   │   └── User.php                 # User model
│   └── Services/
│       ├── SentimentService.php     # NLP & sentiment analysis service
│       └── SpeechToTextService.php  # Speech recognition service
├── config/                          # Application configuration files
├── database/
│   ├── migrations/                  # Database schema migrations
│   └── seeders/                     # Initial database seeders
├── resources/
│   ├── views/                       # Blade templates & UI layouts
│   ├── css/                         # Application CSS
│   └── js/                          # Frontend JS logic
├── routes/
│   └── web.php                      # Application routes
└── storage/
    └── app/public/call_recordings/ # Uploaded audio files
```

---

## 🤝 Contributing

Contributions are welcome! Feel free to fork the repository, make improvements, and submit a Pull Request.

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📄 License

This project is open-sourced under the [MIT License](LICENSE).
