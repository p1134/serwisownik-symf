document.addEventListener("DOMContentLoaded", () => {
    
    const button = document.querySelector(".filter__dropdown");
    const checkboxList = document.querySelector(".checkbox__dropdown-content");
    
    function toggleDropdown(){
        checkboxList.classList.toggle("hidden");
    }
    button.addEventListener("click", toggleDropdown);
})
