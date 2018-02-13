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