/*document.addEventListener('DOMContentLoaded', function() {
    // Example: Target course summary or activity description containers
    var courseContainer = document.querySelector('.course-content');
    if (courseContainer) {
        var inputField = document.createElement('input');
        inputField.type = 'text';
        inputField.name = 'course_keywords';
        inputField.placeholder = 'Enter course keywords here';
        courseContainer.prepend(inputField);
    }

    // For each activity or resource
    document.querySelectorAll('.activity').forEach(function(activity) {
        var inputField = document.createElement('input');
        inputField.type = 'text';
        inputField.name = 'activity_keywords';
        inputField.placeholder = 'Enter activity keywords here';
        activity.appendChild(inputField);
    });
});*/

document.addEventListener('DOMContentLoaded', function() {
    // Retrieve courseid from URL or another source
    var urlParams = new URLSearchParams(window.location.search);
    var courseid = urlParams.get('id'); 
    document.querySelectorAll('.activity').forEach(function(activity) {
        var form = document.createElement('form');
        form.method = 'post';
        form.action = '/local/smartlibrary/view.php';

        var inputField = document.createElement('input');
        inputField.type = 'text';
        inputField.name = 'keywords';
        inputField.placeholder = 'Enter multiple keywords here seperated by commas (Example: html, css, javascript)'
        inputField.className = 'wide-text-field';

        var submitButton = document.createElement('input');
        submitButton.type = 'submit';
        submitButton.value = 'Save';

        var activityIdInput = document.createElement('input');
        activityIdInput.type = 'hidden';
        activityIdInput.name = 'activityid';
        activityIdInput.value = activity.getAttribute('data-id'); // Ensure each activity has a data-attribute for its ID

        var courseIdInput = document.createElement('input');
        courseIdInput.type = 'hidden';
        courseIdInput.name = 'courseid';
        courseIdInput.value = courseid; 

        form.appendChild(courseIdInput);
        form.appendChild(inputField);
        form.appendChild(activityIdInput);
        form.appendChild(submitButton);

        activity.appendChild(form);
    });
    var styleElement = document.createElement('style');
    styleElement.innerHTML = `
        .wide-text-field {
            width: 733px; /* You can adjust the width as per your preference */
        }
    `;
    document.head.appendChild(styleElement);
});