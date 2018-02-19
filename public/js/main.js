/** Navbar */
function toggle() {
    // Get the topnav div
    var x = document.getElementById('topnav');

    // Add or remove responsive class
    if (x.className === 'topnav')
        x.className += " responsive";
    else
        x.className = "topnav";
}

var toggleBtn = document.getElementById('toggleBtn');
toggleBtn.addEventListener('click', function() {
    toggle();
});

/** Profile picture */
var $formUploadedFile = $('input#form_uploadedFile');
var $profilePicture = $('#profile-picture');

$profilePicture.parent('figure').find('> *').click(function () {
    $formUploadedFile.click();
    $formUploadedFile.on('change', function () {
        $(this).parents('form').submit();
    });
});

/** Toggle license */
$('.toggle-license').click(function() {
    $(this).closest('.top').next('.bottom').toggleClass('hidden');
});