document.addEventListener("DOMContentLoaded", () => {
    
    const button = document.querySelector(".filter__dropdown");
    const checkboxList = document.querySelector(".checkbox__dropdown-content");
    
    function toggleDropdown(){
        checkboxList.classList.toggle("hidden");
    }
    button.addEventListener("click", toggleDropdown);
})


document.addEventListener("DOMContentLoaded", () => {
    const button = document.querySelector(".form__button");
    const box = document.querySelector(".content-form");
    const form = document.querySelector(".form__add");

    function toggleForm(){
        box.classList.toggle("hidden-form__form");
        form.classList.toggle("hidden");
        button.classList.toggle("rotated");
    }
    button.addEventListener("click", toggleForm);
})


document.addEventListener("DOMContentLoaded", () =>{
    const button = document.querySelector(".nav__btn");
    const buttonBox = document.querySelector(".nav__btn-box");
    const nav = document.querySelector(".nav__box");
    const profile = document.querySelector(".nav__profile");
    const text = document.querySelectorAll(".nav__hidden");
    // const main = document.querySelector("");

    function toggleNav(){
        nav.classList.toggle("nav__wide");
        profile.classList.toggle("nav__profile--wide");
        buttonBox.classList.toggle("nav__btn-box--wide");
        text.forEach(item => {
            item.classList.toggle("nav__hidden--wide")
        });
    }
    button.addEventListener("click", toggleNav);
})
