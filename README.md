# Audio Transcription Service (Laravel + AWS)

This project is a Laravel-based web application for uploading audio files and automatically transcribing them using **Amazon Transcribe via AWS Lambda**. It includes a frontend file uploader (with multipart upload to S3), queue-based processing, and email delivery of transcription results.

---

## Features

- 📤 Multipart audio upload directly to **Amazon S3** (via presigned URLs)
- ⚙️ Audio transcription via **Amazon Transcribe**, launched through **AWS Lambda**
- 🔁 Asynchronous processing with **Amazon SQS**
- 📬 Email delivery of transcription text
- 🌐 Supported languages are English, Ukrainian, Russian
- 💻 Frontend upload form with progress bar (using **Uppy.js**)
- 🐘 PostgreSQL database
- 🧰 Adminer for database inspection
- 🐳 Dockerized development environment

## Supported audio formats

Amazon Transcribe supports the following audio formats:

- **MP3**
- **MP4**
- **WAV**
- **FLAC**
- (Also: AMR, Ogg, WebM, and others — [see full list](https://docs.aws.amazon.com/transcribe/latest/dg/supported-input.html))

---

## Supported languages

The system currently supports transcription in:

- 🇺🇸 English (`en-US`)
- 🇺🇦 Ukrainian (`uk-UA`)
- 🇷🇺 Russian (`ru-RU`)

---

## Requirements

- Docker
- Docker Compose
- AWS account with:
    - S3 bucket
    - IAM role and credentials
    - Lambda function
    - SQS queue
    - SNS topic

---

## Technologies Used

- **Laravel 12** (PHP framework)
- **Docker & Docker Compose** — Containerized development and deployment
- **PostgreSQL** (Relational database)
- **Amazon S3** (file storage)
- **Amazon Transcribe** (speech-to-text service)
- **Amazon SQS** (message queue)
- **Amazon SNS** (notification service)
- **AWS Lambda** (used to invoke Transcribe via queue event)
- **Tailwind CSS**
- **Uppy.js** (frontend file upload)

---

## How It Works

User uploads audio file via frontend form.

Uppy.js uploads the file directly to S3 using a presigned URL from the backend.

Laravel queues a job to trigger AWS Lambda via SQS

Lambda starts Amazon Transcribe job

When complete, SNS sends a callback via SQS to Laravel

Laravel saves the transcript and emails the result to the user


---

## Development notes

Frontend built with Blade + TailwindCSS + JavaScript

Multipart uploader powered by Uppy.js and presigned S3 requests

Audio transcription is decoupled and processed entirely in the background

Laravel jobs are processed via SQS queue

## Setup (with Docker Compose)

1. **Clone the repository**

`
git clone https://github.com/your-username/aws-transcribe-laravel.git
cd aws-transcribe-laravel
`

2. **Configure environment variables**

Copy .env.example to .env:
`
cp .env.example .env
`
Set AWS, database, and email credentials in .env:
`
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name
`
`
QUEUE_CONNECTION=sqs
SQS_QUEUE=https://sqs.us-east-1.amazonaws.com/123456789012/your-queue
`
`
MAIL_MAILER=smtp
MAIL_HOST=smtp.yourprovider.com
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=yourpassword
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="Transcription Service"
`

3. **Set up Lambda function**

The Lambda function code is located in the start_transcription.py file.

4. **Start Docker containers**
`
docker compose up -d --build
`
This will start:

Laravel app (laravel_app)
Nginx (nginx_server) → http://localhost:8000
PostgreSQL (postgres_db)
Adminer UI (adminer) → http://localhost:8080

5. **Install dependencies and run migrations**
`
docker compose exec app composer install
docker compose exec app php artisan migrate
`
6. **Queue Workers**

To process transcription jobs, you must run both of the following workers:
`
php artisan queue:work sqs --queue=default-queue
`
This worker listens to the main SQS queue and triggers the AWS Lambda function for transcription.
`
php artisan sqs:listen-transcribe

This worker listens to SNS notifications from AWS Transcribe and saves the transcription result to the database.

---
