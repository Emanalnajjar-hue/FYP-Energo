function saveData() {
    const email = document.getElementById('userEmail').value;
    const phone = document.getElementById('userPhone').value;
    const location = document.getElementById('userLocation').value;
    localStorage.setItem('userEmail', email);
    localStorage.setItem('userPhone', phone);
    localStorage.setItem('userLocation', location);
}
