console.log("mods-2016.js loaded");

// Hide the Comments heading (h2 element)
document.querySelector('h2.wp-block-heading').style.display = 'none';

// Hide the "Leave a Reply" heading (h3 element)
document.querySelector('h3.comment-reply-title').style.display = 'none';

// Hide the "Logged in as" paragraph (p element)
document.querySelector('p.logged-in-as').style.display = 'none';

// Hide the "Comment" label (label element)
document.querySelector('label[for="comment"]').style.display = 'none';

// Change the value of the submit button
document.querySelector("#submit").value = ">";

// Hide elements using vanilla JavaScript
document.querySelectorAll(".wp-block-separator").forEach(el => el.style.display = 'none');
document.querySelectorAll(".wp-block-comment-date").forEach(el => el.style.display = 'none');
document.querySelectorAll(".wp-block-comment-edit-link").forEach(el => el.style.display = 'none');
document.querySelectorAll(".wp-block-comment-reply-link").forEach(el => el.style.display = 'none');
document.querySelectorAll(".wp-block-comments-title").forEach(el => el.style.display = 'none');
document.querySelectorAll(".wp-block-post-title").forEach(el => el.style.display = 'none');


document.querySelectorAll(".post-navigation-link-next").forEach(el => el.style.display = 'none');
document.querySelectorAll(".post-navigation-link-previous").forEach(el => el.style.display = 'none');

// Hide a specific deeply nested element
document.querySelector("#wp--skip-link--target > div:nth-child(1) > div > div > div > div").style.display = 'none';


