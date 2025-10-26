// Common JavaScript functions for both personal and shared notes

function viewFile(fileId, isShared = false) {
    const modal = new bootstrap.Modal(document.getElementById('fileModal'));
    const modalBody = document.getElementById('fileModalBody');
    const spinnerClass = isShared ? 'text-success' : 'text-primary';
    
    modalBody.innerHTML = `<div class="text-center"><div class="spinner-border ${spinnerClass}" role="status"><span class="visually-hidden">Loading...</span></div></div>`;
    modal.show();
    
    // Load file content based on file type
    const viewerUrl = isShared ? 'shared_file_viewer.php' : 'file_viewer.php';
    modalBody.innerHTML = `<iframe src="${viewerUrl}?id=${fileId}" width="100%" height="500px" frameborder="0"></iframe>`;
}

function downloadFile(fileId, fileName, isShared = false) {
    const link = document.createElement('a');
    const viewerUrl = isShared ? 'shared_file_viewer.php' : 'file_viewer.php';
    link.href = `${viewerUrl}?id=${fileId}&download=1`;
    link.download = fileName;
    link.click();
}

function deleteFile(fileId, fileName) {
    if (confirm(`Are you sure you want to delete "${fileName}"?`)) {
        fetch('delete_file.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    file_id: fileId
                })
            })
            .then(response => {
                const contentType = response.headers.get("content-type");
                if (contentType && contentType.indexOf("application/json") !== -1) {
                    return response.json();
                } else {
                    throw new Error('Server returned non-JSON response');
                }
            })
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error deleting file. Check console for details.');
            });
    }
}