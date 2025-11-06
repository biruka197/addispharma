const form = document.querySelector('#ocr-form');
const statusEl = document.querySelector('#status');
const resultsSection = document.querySelector('#results');
const extractedTextEl = document.querySelector('#extracted-text');
const questionEl = document.querySelector('#question');
const answerEl = document.querySelector('#answer');
const resetButton = document.querySelector('#reset-button');

const setStatus = (message, isError = false) => {
    statusEl.textContent = message;
    statusEl.classList.toggle('error', isError);
};

form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const fileInput = document.querySelector('#image');
    if (!fileInput.files.length) {
        setStatus('Please choose an image to continue.', true);
        return;
    }

    const formData = new FormData();
    formData.append('image', fileInput.files[0]);

    resultsSection.classList.add('hidden');
    setStatus('Processing image with Gemini 2.5 Pro…');

    try {
        const response = await fetch('process.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            const data = await response.json().catch(() => ({}));
            const message = data.error || `Request failed with status ${response.status}`;
            throw new Error(message);
        }

        const result = await response.json();
        extractedTextEl.textContent = result.extractedText || 'No text detected.';
        questionEl.textContent = result.question || 'Not available.';
        answerEl.textContent = result.answer || 'Not available.';
        resultsSection.classList.remove('hidden');
        setStatus('Complete!');
    } catch (error) {
        console.error(error);
        setStatus(error.message || 'Something went wrong while generating the question.', true);
    }
});

resetButton.addEventListener('click', () => {
    form.reset();
    resultsSection.classList.add('hidden');
    setStatus('');
});
