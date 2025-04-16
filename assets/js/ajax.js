// AJAX functions for file upload, delete, rename, and preview functionalities

// Function to upload a file
function uploadFile(formData) {
    $.ajax({
        url: 'user/upload.php',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        xhr: function() {
            var xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener('progress', function(evt) {
                if (evt.lengthComputable) {
                    var percentComplete = evt.loaded / evt.total;
                    $('#progress-bar').css('width', percentComplete * 100 + '%');
                }
            }, false);
            return xhr;
        },
        success: function(response) {
            // Handle success response
            alert(response.message);
            location.reload();
        },
        error: function(jqXHR, textStatus, errorThrown) {
            // Handle error response
            alert('Error: ' + errorThrown);
        }
    });
}

// Function to delete a file
function deleteFile(fileName) {
    $.ajax({
        url: 'user/delete.php',
        type: 'POST',
        data: { file: fileName },
        success: function(response) {
            // Handle success response
            alert(response.message);
            location.reload();
        },
        error: function(jqXHR, textStatus, errorThrown) {
            // Handle error response
            alert('Error: ' + errorThrown);
        }
    });
}

// Function to rename a file
function renameFile(oldName, newName) {
    $.ajax({
        url: 'user/rename.php',
        type: 'POST',
        data: { oldName: oldName, newName: newName },
        success: function(response) {
            // Handle success response
            alert(response.message);
            location.reload();
        },
        error: function(jqXHR, textStatus, errorThrown) {
            // Handle error response
            alert('Error: ' + errorThrown);
        }
    });
}

// Function to preview a file
function previewFile(fileName) {
    $.ajax({
        url: 'user/preview.php',
        type: 'POST',
        data: { file: fileName },
        success: function(response) {
            // Handle success response
            $('#preview-container').html(response);
            $('#previewModal').modal('show');
        },
        error: function(jqXHR, textStatus, errorThrown) {
            // Handle error response
            alert('Error: ' + errorThrown);
        }
    });
}