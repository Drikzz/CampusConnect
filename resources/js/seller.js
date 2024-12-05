const allSideMenu = document.querySelectorAll('#sidebar .side-menu.top li a');

allSideMenu.forEach(item=> {
	const li = item.parentElement;

	item.addEventListener('click', function () {
		allSideMenu.forEach(i=> {
			i.parentElement.classList.remove('active');
		})
		li.classList.add('active');
	})
});




// TOGGLE SIDEBAR
const menuBar = document.querySelector('#content nav .bx.bx-menu');
const sidebar = document.getElementById('sidebar');

menuBar.addEventListener('click', function () {
	sidebar.classList.toggle('hide');
})







const searchButton = document.querySelector('#content nav form .form-input button');
const searchButtonIcon = document.querySelector('#content nav form .form-input button .bx');
const searchForm = document.querySelector('#content nav form');

searchButton.addEventListener('click', function (e) {
	if(window.innerWidth < 576) {
		e.preventDefault();
		searchForm.classList.toggle('show');
		if(searchForm.classList.contains('show')) {
			searchButtonIcon.classList.replace('bx-search', 'bx-x');
		} else {
			searchButtonIcon.classList.replace('bx-x', 'bx-search');
		}
	}
})





if(window.innerWidth < 768) {
	sidebar.classList.add('hide');
} else if(window.innerWidth > 576) {
	searchButtonIcon.classList.replace('bx-x', 'bx-search');
	searchForm.classList.remove('show');
}


window.addEventListener('resize', function () {
	if(this.innerWidth > 576) {
		searchButtonIcon.classList.replace('bx-x', 'bx-search');
		searchForm.classList.remove('show');
	}
})



const switchMode = document.getElementById('switch-mode');

switchMode.addEventListener('change', function () {
	if(this.checked) {
		document.body.classList.add('dark');
	} else {
		document.body.classList.remove('dark');
	}
})





function previewImages(event) {
	const previewContainer = document.getElementById('image-preview');
	previewContainer.innerHTML = ''; // Clear any existing previews

	const files = event.target.files;
	for (let i = 0; i < files.length; i++) {
		const file = files[i];
		const reader = new FileReader();
		reader.onload = function(e) {
			const img = document.createElement('img');
			img.src = e.target.result;
			img.classList.add('image-preview-item');
			previewContainer.appendChild(img);
		}
		reader.readAsDataURL(file);
	}
}





// edit product  modal
document.addEventListener('DOMContentLoaded', () => {
    // Handle the Edit button click and show the modal
    document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();  // Prevent default link behavior
            
            // Get product data from the button's data attributes
            const productId = button.dataset.productId;
            const productName = button.dataset.productName;
            const productPrice = button.dataset.productPrice;
            const productCategory = button.dataset.productCategory;
            const productDescription = button.dataset.productDescription;

            // Show the modal
            const modal = document.getElementById('crud-modal');
            modal.classList.remove('hidden');
            
            // Populate the modal form with product data
            document.getElementById('product-id').value = productId;
            document.getElementById('name').value = productName;
            document.getElementById('price').value = productPrice;
            document.getElementById('category').value = productCategory;
            document.getElementById('description').value = productDescription;
        });
    });

    // Close modal when the close button is clicked
    document.getElementById('closeModalButton').addEventListener('click', () => {
        const modal = document.getElementById('crud-modal');
        modal.classList.add('hidden');
    });

    // Handle form submission (for example, update product details)
    document.getElementById('edit-product-form').addEventListener('submit', (e) => {
        e.preventDefault();

        const updatedProductData = {
            id: document.getElementById('product-id').value,
            name: document.getElementById('name').value,
            price: document.getElementById('price').value,
            category: document.getElementById('category').value,
            description: document.getElementById('description').value
        };

        // For now, let's log the updated data to the console (you can make an AJAX request here to update the product on the server)
        console.log('Updated Product Data:', updatedProductData);

        // Close the modal after form submission
        const modal = document.getElementById('crud-modal');
        modal.classList.add('hidden');
    });
});






  // method modal

    // Get the modal element
    // Get the modal and buttons
const modal = document.getElementById('crypto-modal');
const openModalButton = document.getElementById('refillBtn');
const closeModalButton = document.getElementById('closeModalBtn');

// Function to show the modal
const openModal = () => {
    modal.classList.remove('hidden'); // Remove 'hidden' to show the modal
};

// Function to hide the modal
const closeModal = () => {
    modal.classList.add('hidden'); // Add 'hidden' to hide the modal
};

// Event listener to open the modal when the "Refill" button is clicked
openModalButton.addEventListener('click', openModal);

// Event listener to close the modal when the close button (X) is clicked
closeModalButton.addEventListener('click', closeModal);

// Optional: Close the modal if the user clicks outside of it (on the darkened background)
modal.addEventListener('click', (event) => {
    if (event.target === modal) {
        closeModal();
    }
});







// JavaScript for Withdraw Modal

const withdrawModal = document.getElementById('withdraw-modal');
const openWithdrawModalButton = document.getElementById('withdrawBtn');
const closeWithdrawModalButton = document.getElementById('closeWithdrawModalBtn');

// Function to open the Withdraw modal
const openWithdrawModal = () => {
    withdrawModal.classList.remove('hidden'); // Show modal
};

// Function to close the Withdraw modal
const closeWithdrawModal = () => {
    withdrawModal.classList.add('hidden'); // Hide modal
};

// Event listener for opening the Withdraw modal when the button is clicked
openWithdrawModalButton.addEventListener('click', openWithdrawModal);

// Event listener for closing the Withdraw modal when the close button (X) is clicked
closeWithdrawModalButton.addEventListener('click', closeWithdrawModal);

// Optional: Close the modal if the user clicks outside of it (on the darkened background)
withdrawModal.addEventListener('click', (event) => {
    if (event.target === withdrawModal) {
        closeWithdrawModal();
    }
});

