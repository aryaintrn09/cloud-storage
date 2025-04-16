document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("uploadForm");
    const bar = document.getElementById("progressBar");

    form?.addEventListener("submit", function (e) {
        e.preventDefault();
        const xhr = new XMLHttpRequest();
        const formData = new FormData(form);

        xhr.upload.addEventListener("progress", function (e) {
            const percent = (e.loaded / e.total) * 100;
            bar.value = percent;
        });

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                location.reload();
            }
        };

        xhr.open("POST", "../includes/upload.php", true);
        xhr.send(formData);
    });
});
