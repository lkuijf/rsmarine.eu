/***** To Top Button *************************/
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
/*********************************************/
/*2*/
/***** Fade in elements when in viewport *****/
// Beware of user has disabled JS; do not hide elements using CSS
const fadeElms = document.querySelectorAll('.contentWrapper .inner');
const observerOptions = {
    root: null,
    threshold: 0.2
};
function observerCallback(entries, observer) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            // entry.target.classList.replace('fadeOut', 'fadeIn');
            entry.target.style.opacity = 1;
            entry.target.style.transform = "translateY(0px)";
        } else {
            // entry.target.classList.replace('fadeIn', 'fadeOut');
        }
    });
}
fadeElms.forEach(el => {
    el.style.opacity = 0; // Beware of user has disabled JS; do not hide elements using CSS
    el.style.transform = "translateY(50px)";
});
setTimeout(() => { // using setTimeout for elements that are directly in viewport, so they show the effect
    const observer = new IntersectionObserver(observerCallback, observerOptions);
    fadeElms.forEach(el => {
        el.style.transition = "opacity 0.7s ease-in, transform 0.7s ease-out";
        observer.observe(el);
    });
}, 1);
/*************************************************/