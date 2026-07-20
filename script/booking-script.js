document.addEventListener('DOMContentLoaded', function () {
    const productDiv = document.querySelector('.summary-product');
    const locationSelect = document.getElementById('location-select');
    const checkBtn = document.getElementById('check-availability-btn');
    const bookBtn = document.getElementById('book-btn');
    const messageDiv = document.getElementById('availability-message');

    const pickupDate = document.getElementById('pickup-date');
    const returnDate = document.getElementById('return-date');
    const addonsCheckboxes = document.querySelectorAll('.addon-checkbox');

    const daysCountSpan = document.getElementById('days-count');
    const baseTotalSpan = document.getElementById('base-total');
    const addonsTotalSpan = document.getElementById('addons-total');
    const grandTotalSpan = document.getElementById('grand-total');

    let dailyPrice = 0;
    let productLocation = '';
    let productId = 0;

    if (productDiv) {
        dailyPrice = parseFloat(productDiv.getAttribute('data-price')) || 0;
        productLocation = productDiv.getAttribute('data-location');
        productId = document.getElementById('backend-product-id').value;
    }

    function calculateTotal() {
        let days = 1;
        if (pickupDate.value && returnDate.value) {
            const start = new Date(pickupDate.value);
            const end = new Date(returnDate.value);
            const diffTime = end - start;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            days = diffDays > 0 ? diffDays : 1;
        }

        const baseTotal = dailyPrice * days;

        let addonsTotal = 0;
        addonsCheckboxes.forEach(chk => {
            if (chk.checked) {
                addonsTotal += parseFloat(chk.getAttribute('data-price')) * days;
            }
        });

        daysCountSpan.innerText = days;
        baseTotalSpan.innerText = "$" + baseTotal.toFixed(2);
        addonsTotalSpan.innerText = "$" + addonsTotal.toFixed(2);
        grandTotalSpan.innerText = "$" + (baseTotal + addonsTotal).toFixed(2);

        return baseTotal + addonsTotal;
    }

    returnDate.addEventListener('change', function () {
        if (pickupDate.value && this.value) {
            const start = new Date(pickupDate.value);
            const end = new Date(this.value);
            if (end < start) {
                alert("Error: Return date cannot be before Pickup date!");
                this.value = "";
                return;
            }
            calculateTotal();
        }
    });

    pickupDate.addEventListener('change', calculateTotal);
    addonsCheckboxes.forEach(chk => { chk.addEventListener('change', calculateTotal); });

    checkBtn.addEventListener('click', function () {
        const selectedLocation = locationSelect.value;
        const pDate = pickupDate.value;

        messageDiv.style.display = 'block';

        if (!selectedLocation) {
            messageDiv.style.color = '#d35400';
            messageDiv.innerText = "Please select a location first.";
            bookBtn.disabled = true;
            return;
        }

        if (!pDate) {
            messageDiv.style.color = '#d35400';
            messageDiv.innerText = "Please select a pickup date.";
            bookBtn.disabled = true;
            return;
        }

        checkBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Checking...';
        bookBtn.disabled = true;

        setTimeout(() => {
            checkBtn.innerHTML = 'Check Availability <i class="fas fa-check"></i>';


            if (productLocation === selectedLocation) {
                messageDiv.style.color = 'green';
                messageDiv.innerHTML = `<i class="fas fa-check-circle"></i> Great! Product is available in <strong>${selectedLocation}</strong>.`;

                bookBtn.disabled = false;
                bookBtn.style.background = "linear-gradient(to right, #10482D, #02831e)";
                bookBtn.style.cursor = "pointer";
            } else {
                messageDiv.style.color = 'red';
                messageDiv.innerHTML = `<i class="fas fa-times-circle"></i> Sorry, this product is only available in <strong>${productLocation}</strong>.`;

                bookBtn.disabled = true;
                bookBtn.style.background = "#ccc";
                bookBtn.style.cursor = "not-allowed";
            }

        }, 800);
    });

    bookBtn.addEventListener('click', function () {
        const finalTotal = calculateTotal();
        const rDate = returnDate.value;

        if (!rDate) {
            alert("Please select a return date.");
            return;
        }

        window.location.href = `payment_summary.php?product_id=${productId}&pickup=${pickupDate.value}&return=${rDate}&total=${finalTotal}`;
    });
});