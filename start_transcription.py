import json
import boto3
import os

transcribe = boto3.client('transcribe')
s3_bucket = os.environ['S3_BUCKET']

SUPPORTED_FORMATS = ['mp3', 'mp4', 'wav', 'flac', 'ogg', 'amr', 'webm']

def get_media_format(key):
    ext = key.split('.')[-1].lower()
    if ext in SUPPORTED_FORMATS:
        return ext
    raise ValueError(f"Unsupported media format: {ext}")

def lambda_handler(event, context):
    print("Received event:", json.dumps(event))

    try:
        message = event['Records'][0]['body']
        data = json.loads(message)
    except (KeyError, IndexError, json.JSONDecodeError) as e:
        raise ValueError(f"Invalid SQS message format: {e}")

    key = data.get('s3_key')
    if not key:
        raise ValueError("S3 key not provided in SQS message")

    media_format = get_media_format(key)
    job_name = f"transcribe-{key.replace('/', '_')}"
    media_uri = f"s3://{s3_bucket}/{key}"

    response = transcribe.start_transcription_job(
        TranscriptionJobName=job_name,
        Media={'MediaFileUri': media_uri},
        MediaFormat=media_format,
        OutputBucketName=s3_bucket,
        Settings={
            'ShowSpeakerLabels': False
        },
        OutputKey=f"transcriptions/{job_name}.json",
        IdentifyLanguage=True,
        LanguageOptions=['en-US', 'ru-RU', 'uk-UA']
    )

    print("Started Transcribe job:", response)

    return {
        'statusCode': 200,
        'body': json.dumps('Transcribe job started')
    }
