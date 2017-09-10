/* Toggle between adding and removing the "responsive" class to topnav when the user clicks on the icon */
function menuToggle() {
    var x = document.getElementById("myTopnav");
    if (x.className === "topnav") {
        x.className += " responsive";
    } else {
        x.className = "topnav";
    }
}

// ArtWorks grid

ArtWorksGridInit = function () {
    var grid = document.getElementById('art-works-list');
    imagesLoaded( grid, function() {
        var msnry = new Masonry( grid, {
            itemSelector: ".art-work",
            columnWidth: '.art-work',
            gutter: '.gutter-sizer',
            percentPosition: true
        });
    });
};

// View single art work on art works page

showArtWork = function(event) {
    console.log(this.width.toString());
    this.parentElement.style.width = '100%';
    ArtWorksGridInit();
    this.classList.add("big-picture");
};


