/* To Top Button */
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
/*******/

/* Fade in elements when in viewport */
const observerOptions = {
    root: null,
    rootMargin: "0px",
    threshold: 0.2
};

function observerCallback(entries, observer) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            // entry.target.classList.replace('fadeOut', 'fadeIn');
            entry.target.classList.add('fadeIn');
        } else {
            // entry.target.classList.replace('fadeIn', 'fadeOut');
        }
    });
}

const observer = new IntersectionObserver(observerCallback, observerOptions);

const fadeElms = document.querySelectorAll('.contentWrapper .inner');
console.log(fadeElms.length);
fadeElms.forEach(el => observer.observe(el));
/*******/