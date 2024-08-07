document.addEventListener('DOMContentLoaded', () => {
    const folderList = document.getElementById('folder-list');
    const folderSelect = document.getElementById('folder-select');
    const fileList = document.getElementById('file-list');

    function fetchFolders() {
        fetch('get_folders.php')
            .then(response => response.json())
            .then(folders => {
                folderList.innerHTML = '';
                folderSelect.innerHTML = '<option value="">Select Folder</option>';
                folders.forEach(folder => {
                    folderList.innerHTML += `<li>${folder.name}</li>`;
                    folderSelect.innerHTML += `<option value="${folder.id}">${folder.name}</option>`;
                });
            });
    }

    document.getElementById('create-folder-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const folderName = document.getElementById('folder-name').value;
        fetch('create_folder.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ folder_name: folderName })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                fetchFolders();
                document.getElementById('folder-name').value = '';
            }
        });
    });

    document.getElementById('upload-file-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const folderId = document.getElementById('folder-select').value;
        const fileName = document.getElementById('file-name').value;
        const fileContent = document.getElementById('file-content').value;

        fetch('upload_file.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                folder_id: folderId,
                file_name: fileName,
                file_content: fileContent
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                fetchFiles(folderId);
                document.getElementById('file-name').value = '';
                document.getElementById('file-content').value = '';
            }
        });
    });

    function fetchFiles(folderId) {
        fetch(`get_files.php?folder_id=${folderId}`)
            .then(response => response.json())
            .then(files => {
                fileList.innerHTML = '';
                files.forEach(file => {
                    fileList.innerHTML += `<li>${file.name}</li>`;
                });
            });
    }

    folderSelect.addEventListener('change', function() {
        const folderId = this.value;
        if (folderId) {
            fetchFiles(folderId);
        } else {
            fileList.innerHTML = '';
        }
    });

    fetchFolders();
});
