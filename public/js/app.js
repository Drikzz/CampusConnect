import 'flowbite';
import '../js/bootstrap';
// import '../css/seller.css';

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

    //modal for the user type
    $('#continueBtn').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const selectedType = $('input[name="userType"]:checked').val();
        if (!selectedType) {
            alert('Please select a user type');
            return;
        }

        window.location.href = $('#userTypeForm').data(`route-${selectedType}`);
    });

    // picture upload in registration
    // Image upload preview functionality
    function setupImagePreview(inputId, previewId, uploadContainerId, previewContainerId, changePhotoBtnId) {
        const $input = $(`#${inputId}`);
        const $preview = $(`#${previewId}`);
        const $uploadContainer = $(`#${uploadContainerId}`);
        const $previewContainer = $(`#${previewContainerId}`);
        const $changePhotoBtn = $(`#${changePhotoBtnId}`);

        // Set initial states for profile
        $previewContainer.addClass('hidden').removeClass('flex');
        $changePhotoBtn.addClass('hidden').removeClass('flex');

        // Handle file selection
        $input.on('change', function(e) {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $preview.attr('src', e.target.result);
                    $uploadContainer.addClass('hidden');
                    $previewContainer.removeClass('hidden').addClass('flex');
                    $changePhotoBtn.removeClass('hidden').addClass('flex');
                }
                reader.readAsDataURL(file);
            }
        });

        // Handle change photo button
        $changePhotoBtn.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            $input.click();
        });
    }

    // Initialize preview for each image upload
    setupImagePreview(
        'profile_picture', 
        'preview',
        'uploadContainer', 
        'previewContainer',
        'changePhotoBtn'
    );

    setupImagePreview(
        'wmsu_id_front',
        'previewFront',
        'uploadContainerFront',
        'previewContainerFront', 
        'changePhotoBtnFront'
    );

    setupImagePreview(
        'wmsu_id_back',
        'previewBack', 
        'uploadContainerBack',
        'previewContainerBack',
        'changePhotoBtnBack'
    );

    // dashboard buyer 
    $('[data-collapse-toggle]').on('click', function() {
        const target = $('#' + $(this).attr('aria-controls'));
        target.toggleClass('hidden');
    });
    
    $('.load-content').on('click', function(e) {
        e.preventDefault();
        const target = $(this).data('target');
        $('.tab-content').addClass('hidden'); // Hide all tab contents
        $('#' + target).removeClass('hidden'); // Show the selected tab content
    });

    // Trigger the profile tab by default if the URL contains '/dashboard/profile'
    if (window.location.pathname.includes('/dashboard/profile')) {
        $('a[data-target="profile"]').trigger('click');
    } else if (window.location.pathname.includes('/dashboard/orders')) {
        $('a[data-target="pending"]').trigger('click');
    }
    
    //dashboard picture upload
    $('#file-input').on('change', function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();

            reader.onload = function (e) {
                $('#preview-img').attr('src', e.target.result); // Set the preview image source
                $('#current-picture').addClass('hidden'); // Hide current picture
                $('#preview-picture').removeClass('hidden'); // Show preview
            };

            reader.readAsDataURL(file); // Convert file to DataURL
        }

        // Reset the file input's value so the user can reselect the same file if needed
        $(this).val('');
    });

    // Reset button to cancel the upload and revert back to current image
    $('#reset-upload').on('click', function () {
        $('#preview-picture').addClass('hidden'); // Hide preview
        $('#current-picture').removeClass('hidden'); // Show current picture
    });

    // profileCard wmsu front and back
    // WMSU ID Front upload
    $('#wmsu_id_front_input').on('change', function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();

            reader.onload = function (e) {
                $('#wmsu_id_front_preview_img').attr('src', e.target.result); // Set the preview image source
                $('#current-wmsu-id-front').addClass('hidden'); // Hide current picture
                $('#preview-wmsu-id-front').removeClass('hidden'); // Show preview
            };

            reader.readAsDataURL(file); // Convert file to DataURL
        }

        // Reset the file input's value so the user can reselect the same file if needed
        $(this).val('');
    });

    // Reset button to cancel the upload and revert back to current image
    $('#reset-wmsu-id-front-upload').on('click', function () {
        $('#preview-wmsu-id-front').addClass('hidden'); // Hide preview
        $('#current-wmsu-id-front').removeClass('hidden'); // Show current picture
    });

    // WMSU ID Back upload
    $('#wmsu_id_back_input').on('change', function () {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();

            reader.onload = function (e) {
                $('#wmsu_id_back_preview_img').attr('src', e.target.result); // Set the preview image source
                $('#current-wmsu-id-back').addClass('hidden'); // Hide current picture
                $('#preview-wmsu-id-back').removeClass('hidden'); // Show preview
            };

            reader.readAsDataURL(file); // Convert file to DataURL
        }

        // Reset the file input's value so the user can reselect the same file if needed
        $(this).val('');
    });

    // Reset button to cancel the upload and revert back to current image
    $('#reset-wmsu-id-back-upload').on('click', function () {
        $('#preview-wmsu-id-back').addClass('hidden'); // Hide preview
        $('#current-wmsu-id-back').removeClass('hidden'); // Show current picture
    });

    //checkout page - replace the existing checkout code with this:
    if (document.getElementById('quantity')) { // Only run on pages with quantity element
        const quantity = document.getElementById('quantity');
        const formQuantity = document.getElementById('form-quantity');
        const subtotalElement = document.getElementById('subtotal');
        const originalPriceElement = document.getElementById('original-price');
        const discountAmountElement = document.getElementById('discount-amount');
        const totalElement = document.getElementById('total');
        const formTotal = document.getElementById('form-total');

        // Get values from data attributes
        const maxStock = parseInt(quantity.dataset.maxStock);
        const originalPrice = parseFloat(quantity.dataset.originalPrice);
        const discountedPrice = parseFloat(quantity.dataset.discountedPrice);

        // Format price helper
        const formatPrice = (num) => '₱' + num.toFixed(2);

        // Update all prices and quantities
        function updateQuantityAndPrices(newValue) {
            if (newValue >= 1 && newValue <= maxStock) {
                quantity.value = newValue;
                formQuantity.value = newValue;

                const subtotal = newValue * discountedPrice;
                const originalTotal = newValue * originalPrice;
                const discountAmount = originalTotal - subtotal;

                subtotalElement.textContent = formatPrice(subtotal);
                originalPriceElement.textContent = formatPrice(originalTotal);
                discountAmountElement.textContent = '-' + formatPrice(discountAmount);
                totalElement.textContent = formatPrice(subtotal);
                formTotal.value = subtotal.toFixed(2);
            }
        }

        // Event listeners
        $('#decrement-button').on('click', function(e) {
            e.preventDefault();
            updateQuantityAndPrices(parseInt(quantity.value) - 1);
        });

        $('#increment-button').on('click', function(e) {
            e.preventDefault();
            updateQuantityAndPrices(parseInt(quantity.value) + 1);
        });

        // Initialize with starting value
        updateQuantityAndPrices(1);
    }

    $('#menuToggle').on('click', function() {
        $('#sidebar').toggleClass('sidebar-collapsed');
        $(this).toggleClass('rotate-90');
    });

    // TRIGGERS
    // Trigger change event on page load to set initial state
    $('input[type="radio"][name="option"]:checked').trigger('change');
});