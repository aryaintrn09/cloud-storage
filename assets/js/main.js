document.addEventListener('DOMContentLoaded', function() {
    const uploadForm = document.getElementById('uploadForm');
    const fileInput = document.getElementById('fileInput');
    const progressBar = document.getElementById('progressBar');
    const feedback = document.getElementById('feedback');

    uploadForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const file = fileInput.files[0];

        if (validateFile(file)) {
            uploadFile(file);
        } else {
            feedback.textContent = 'Invalid file. Please ensure it is a PNG, JPG, PDF, or audio file and under 5MB.';
        }
    });

    function validateFile(file) {
        const validExtensions = ['image/png', 'image/jpeg', 'application/pdf', 'audio/mpeg', 'audio/wav'];
        const maxSize = 5 * 1024 * 1024; // 5MB
        return validExtensions.includes(file.type) && file.size <= maxSize;
    }

    function uploadFile(file) {
        const formData = new FormData();
        formData.append('file', file);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'user/upload.php', true);

        xhr.upload.onprogress = function(event) {
            if (event.lengthComputable) {
                const percentComplete = (event.loaded / event.total) * 100;
                progressBar.style.width = percentComplete + '%';
                progressBar.setAttribute('aria-valuenow', percentComplete);
            }
        };

        xhr.onload = function() {
            if (xhr.status === 200) {
                feedback.textContent = 'File uploaded successfully!';
                progressBar.style.width = '0%'; // Reset progress bar
            } else {
                feedback.textContent = 'Upload failed. Please try again.';
            }
        };

        xhr.send(formData);
    }

    // Additional functions for delete, rename, and preview can be added here
});