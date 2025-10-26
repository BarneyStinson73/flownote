$(document).ready(function() {
    const container = $('#uploadContainer');
    const fileInput = $('#fileInput');
    const uploadText = $('#uploadText');
    const submitBtn = $('#submitBtn');
    
    // File selection handler
    fileInput.on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            updateUploadText(file.name);
            submitBtn.prop('disabled', false);
        } else {
            resetUploadText();
            submitBtn.prop('disabled', true);
        }
    });

    // Drag and drop handlers
    container.on('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        container.addClass('drag-over');
    });

    container.on('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        container.removeClass('drag-over');
    });

    container.on('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        container.removeClass('drag-over');
        
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            fileInput[0].files = files;
            updateUploadText(files[0].name);
            submitBtn.prop('disabled', false);
        }
    });

    // Category selection
    $('.category-option').on('click', function() {
        $('.category-option').removeClass('selected');
        $(this).addClass('selected');
        $('#selectedCategory').val($(this).data('category'));
    });

    function updateUploadText(fileName) {
        const shortName = fileName.length > 25 ? fileName.substring(0, 22) + '...' : fileName;
        uploadText.html(`📤 Ready to Upload<br><small>${shortName}</small>`);
    }

    function resetUploadText() {
        uploadText.html('📁 Click or Drop<br>file to upload');
    }

    // Form submission with loading state
    $('#uploadForm').on('submit', function() {
        submitBtn.prop('disabled', true).html('⏳ Processing...');
        uploadText.html('⏳ Processing<br>Please wait...');
    });

    // Final upload form submission
    $('#finalUploadForm').on('submit', function() {
        $(this).find('button[type="submit"]').prop('disabled', true).html('⏳ Saving...');
    });
});