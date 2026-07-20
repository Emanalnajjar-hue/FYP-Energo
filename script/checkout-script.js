function calculateTotal() {

    const pickupInput = document.getElementById('pickup-date').value;
    const returnInput = document.getElementById('return-date').value;


    if (pickupInput && returnInput) {
        const pickup = new Date(pickupInput);
        const returnDate = new Date(returnInput);


        const diffTime = returnDate - pickup;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));


        if (diffDays >= 0) {

            const dailyRate = 15.00;
            const extensionCables = 3.50;
            const basePrice = diffDays * dailyRate;
            const total = basePrice + extensionCables;


            document.getElementById('duration-display').innerText = diffDays + " Days";
            document.getElementById('days-count').innerText = diffDays;
            document.getElementById('base-price-display').innerText = "$" + basePrice.toFixed(2);
            document.getElementById('total-amount-display').innerText = "$" + total.toFixed(2);
        } else {

            alert("Please select a return date that is after the pickup date.");
        }
    }
}