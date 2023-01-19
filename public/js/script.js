// console.log('test');
const toTopBtn = document.querySelector('#toTop');
toTopBtn.addEventListener('click', (e) => {
    e.preventDefault();
    window.scrollTo(0, 0);
});
if(window.scrollY > 800) {
    toTopBtn.classList.add('show');
}
window.addEventListener('scroll', (e) => {
    let fromTop = window.scrollY + 400; // +400 so it is more user friendly
    if(fromTop > 800) toTopBtn.classList.add('show');
    else toTopBtn.classList.remove('show');
});