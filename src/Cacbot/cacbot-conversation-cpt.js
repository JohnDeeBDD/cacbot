/* global cacbot_data */

console.log("cacbot-conversation-cpt.js loaded");
console.log(cacbot_data);

document.addEventListener('DOMContentLoaded', function () {
    // Select the comment form
    var commentForm = document.getElementById('commentform');
    if (commentForm) {
        var newButton = document.getElementById('cacbot-dall-e-3-button');
        if (newButton) {
            newButton.addEventListener('click', function () {
                var currentAction = commentForm.action;
                var url = new URL(currentAction);
                url.searchParams.set('model', 'dall-e-3');
                commentForm.action = url.toString();

                var submitButton = commentForm.querySelector('[type="submit"]');
                if (submitButton) {
                    submitButton.click();
                } else {
                    console.log("Submit button not found");
                }
            });
        } else {
            console.log("New button not found");
        }
    }

    // Event listener for the archive button
    var archiveButton = document.getElementById('cacbot-archive-button');
    if (archiveButton) {
        archiveButton.addEventListener('click', function (event) {
            event.preventDefault(); // Prevent default behavior

            // Dynamically create a new form element
            var form = document.createElement('form');
            form.method = 'POST'; // You can change this to 'GET' if needed
            form.action = window.location.origin + window.location.pathname + '?model=archive'; // Set the action with the model=archive

            // Create and append hidden inputs for nonce and post_id
            var nonceInput = document.createElement('input');
            nonceInput.type = 'hidden';
            nonceInput.name = 'nonce';
            nonceInput.value = cacbot_data.nonce;
            form.appendChild(nonceInput);

            var postIdInput = document.createElement('input');
            postIdInput.type = 'hidden';
            postIdInput.name = 'post_id';
            postIdInput.value = cacbot_data.post_id;
            form.appendChild(postIdInput);

            // Append the form to the body and submit it
            document.body.appendChild(form);
            form.submit();
        });
    } else {
        console.log("Archive button not found");
    }

    var label = document.querySelector('label[for="comment"]');
});
