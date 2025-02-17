import 'flowbite';
// import ApexCharts from 'apexcharts';


$(document).ready(function () {
    
    // Select all elements with the class 'bookmarked' or 'unbookmark'
    $(".bookmarked, .unbookmark").on("click", function (e) {
        e.preventDefault();
    
        const isBookmarked = $(this).hasClass("bookmarked");
        const parentContainer = $(this).closest(".relative");
    
        if (isBookmarked) {
            parentContainer.find(".bookmarked").addClass("hidden");
            parentContainer.find(".unbookmark").removeClass("hidden");
        } else {
            parentContainer.find(".bookmarked").removeClass("hidden");
            parentContainer.find(".unbookmark").addClass("hidden");
        }
    
    });

    // any and all radio buttons
    $('input[type="radio"][name="option"]').on('change', function () {
        if ($('#anyRadio').is(':checked')) {
            $('label[for="anyRadio"]').addClass('ring-2 ring-primary-color bg-primary-color');
            $('#anyText').removeClass('text-gray-400').addClass('text-black');
        } else {
            $('label[for="anyRadio"]').removeClass('ring-2 ring-primary-color bg-primary-color');
            $('#anyText').removeClass('text-black').addClass('text-gray-400');
        }

        if ($('#allRadio').is(':checked')) {
            $('label[for="allRadio"]').addClass('ring-2 ring-primary-color bg-primary-color');
            $('#allText').removeClass('text-gray-400').addClass('text-black');
        } else {
            $('label[for="allRadio"]').removeClass('ring-2 ring-primary-color bg-primary-color');
            $('#allText').removeClass('text-black').addClass('text-gray-400');
        }
    });

    // mini images to main image show
    const images = [
        $('#img1').attr('src'),
        $('#img2').attr('src'),
        $('#img3').attr('src')
    ];
    let currentIndex = 0;

    const mainImage = $('#mainImage');
    const prevButton = $('#prevButton');
    const nextButton = $('#nextButton');
    
    prevButton.on('click', function () {
        currentIndex = (currentIndex > 0) ? currentIndex - 1 : images.length - 1;
        mainImage.attr('src', images[currentIndex]);
    });
    
    nextButton.on('click', function () {
        currentIndex = (currentIndex < images.length - 1) ? currentIndex + 1 : 0;
        mainImage.attr('src', images[currentIndex]);
    });

    // Click event for mini images to set them as the main image
    $('.w-28 img').on('click', function () {
        const clickedImageSrc = $(this).attr('src');
        mainImage.attr('src', clickedImageSrc);
        currentIndex = images.indexOf(clickedImageSrc);
    });
    
    // Selecting the tab in the prod_details
    // Set initial state for the first tab
    
    // Hide all tab contents initially
    $('.tabcontent').hide();
    
    // Show the first tab content
    $('.tab').first().removeClass('border-transparent').addClass('border-black');
    $('.tabcontent').first().show();

    // Selecting the tab in the prod_details
    $('.tab').on('click', function () {
        // Hide all tab contents
        $('.tabcontent').hide();
        
        // Remove the border from all tabs
        $('.tab').removeClass('border-primary-color').addClass('border-transparent');
        
        // Show the current tab content of the clicked tab
        var tabName = $(this).attr('data-tab');
        $('#' + tabName).show();

        // Add the border to the selected tab
        $(this).removeClass('border-transparent').addClass('border-black');
    });

    // TRIGGERS
    // Trigger change event on page load to set initial state
    $('input[type="radio"][name="option"]:checked').trigger('change');
});