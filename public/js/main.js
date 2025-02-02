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
    const table = document.querySelector(".content-table");
    const tableRepair = document.querySelector(".content-table--repair");

    function toggleForm(){
        box.classList.toggle("hidden-form__form");
        table.classList.toggle("hidden-form__table");
        button.classList.toggle("rotated");
        tableRepair.classList.toggle("hidden-form__table--repair");
    }
    button.addEventListener("click", toggleForm);
})
