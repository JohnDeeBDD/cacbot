console.log("mods-2016.js loaded");

document.querySelector("#colophon").style.display = 'none';
// Hide the Comments heading (h2 element)
//document.querySelector('h2.wp-block-heading').style.display = 'none';

// Hide the "Logged in as" paragraph (p element)
document.querySelector('p.logged-in-as').style.display = 'none';

// Hide the "Comment" label (label element)
document.querySelector('label[for="comment"]').style.display = 'none';

// Change the value of the submit button
document.querySelector("#submit").value = "SUBMIT";



jQuery(document).ready(function() {

    jQuery("#reply-title").hide();

    jQuery(".comment-reply-link").hide();

    jQuery(".comment-metadata").hide();

    jQuery(".comments-title").hide();
    jQuery(".post-edit-link").hide();
    // Change the border-top of an element with id 'myElement'
    jQuery('.comment-list article').css('border-top', 'none');
    jQuery(".author-info").hide();
    jQuery(".entry-title").hide();
    jQuery('.site-main article').css('margin', '0px');

    jQuery('#respond').css('margin', '0px');
    jQuery('#respond').css('padding', '0px');
    jQuery('#comment-form').css('padding', '0px');
    jQuery('.comment-body').css('padding', '15px');
    jQuery(".comment-form-comment").css('margin-bottom', '0px');
    jQuery('.entry-content').css('margin-left', '0px');
    jQuery('.entry-content').css('width', '100%');
    jQuery('#masthead').css('padding', '10px');

    document.getElementById('comment').placeholder = 'Talk to the Assistant.';

    document.getElementById('comment').rows = 4;


});
document.addEventListener('DOMContentLoaded', function() {
    // Inject CSS for the spinner and text effects
    const style = document.createElement('style');
    style.innerHTML = `
        .spinner {
            width: 24px;
            height: 24px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            display: inline-block;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .thinking-text {
            font-size: 16px;
            margin-left: 10px;
            opacity: 0;
            animation: fadeInOut 1.5s ease-in-out infinite;
            display: inline-block;
        }

        @keyframes fadeInOut {
            0%, 100% { opacity: 0; }
            50% { opacity: 1; }
        }
    `;
    document.head.appendChild(style);

    const submitButton = document.getElementById('submit');

    if (submitButton) {
        const form = submitButton.closest('form');

        // Create spinner element
        const loadingSpinner = document.createElement('div');
        loadingSpinner.className = 'spinner';
        loadingSpinner.style.display = 'none'; // Initially hidden

        // Create thinking text element
        const thinkingText = document.createElement('span');
        thinkingText.className = 'thinking-text';
        thinkingText.textContent = 'Thinking...';
        thinkingText.style.display = 'none'; // Initially hidden

        // Insert the spinner and text next to the submit button
        submitButton.parentNode.insertBefore(loadingSpinner, submitButton.nextSibling);
        submitButton.parentNode.insertBefore(thinkingText, loadingSpinner.nextSibling);

        form.addEventListener('submit', function(event) {
            // Prevent multiple submissions
            submitButton.disabled = true;

            // Hide the submit button and show spinner with text
            submitButton.style.display = 'none';
            loadingSpinner.style.display = 'inline-block';
            thinkingText.style.display = 'inline-block';
        });
    }
});
