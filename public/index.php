<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Question Generator</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <main class="container">
        <header>
            <h1>Exam Question Generator</h1>
            <p>Upload an image containing educational content and let Gemini 2.5 Pro create a question with the answer.</p>
        </header>

        <section class="card">
            <form id="ocr-form" enctype="multipart/form-data">
                <label class="file-input" for="image">Select an image</label>
                <input type="file" id="image" name="image" accept="image/*" required>

                <div class="controls">
                    <button type="submit" class="primary">Generate</button>
                    <button type="reset" class="secondary" id="reset-button">Clear</button>
                </div>
            </form>

            <div id="status" class="status" role="status" aria-live="polite"></div>
        </section>

        <section id="results" class="card hidden">
            <h2>Extracted Text</h2>
            <pre id="extracted-text" class="mono"></pre>

            <h2>Generated Exam Question</h2>
            <div class="qa">
                <p><strong>Question:</strong> <span id="question"></span></p>
                <p><strong>Answer:</strong> <span id="answer"></span></p>
            </div>
        </section>

        <footer>
            <p>Set the <code>GEMINI_API_KEY</code> environment variable before using the tool. Images never leave your browser except for the OCR request.</p>
        </footer>
    </main>

    <script src="app.js"></script>
</body>
</html>
