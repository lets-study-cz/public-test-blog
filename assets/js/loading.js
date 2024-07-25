window.addEventListener('load', () => {
    document.getElementById('loader').style.opacity = '0';
    setTimeout(() => {
        document.getElementById('loader').style.display = 'none';
    }, 1000);
});