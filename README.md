# Exam Question Generator (Gemini 2.5 Pro OCR)

This lightweight PHP application lets you upload an instructional image, performs OCR with the Gemini 2.5 Pro API, and returns a study question with its answer derived from the detected text.

## Prerequisites

- PHP 8.1 or later with `curl` extension enabled.
- A Google Gemini API key with access to the `gemini-2.5-pro-latest` model.

## Configuration

1. Export your API key before starting your PHP server:
   ```bash
   export GEMINI_API_KEY="your-key-here"
   ```
2. Serve the `public/` directory (for example with PHP's built-in server):
   ```bash
   php -S localhost:8000 -t public
   ```

## Usage

1. Open `http://localhost:8000` in your browser.
2. Select a JPG, PNG, WEBP, or GIF image that contains the learning material.
3. Click **Generate**. The app will upload the image to the PHP backend, which
   forwards it to the Gemini 2.5 Pro API and returns:
   - the extracted text,
   - an automatically created exam-style question, and
   - the corresponding answer.
4. If the OCR fails or Gemini cannot return structured JSON, an error message
   will be displayed instead.

## Notes

- The request instructs Gemini to return JSON; if the model produces plain text
  instead, the response will be surfaced as an error so you can adjust the prompt.
- Images are only transmitted to Gemini for processing and are not stored on the server.
