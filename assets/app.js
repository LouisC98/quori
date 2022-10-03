// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.scss';

const burger = document.getElementById('burger');
const menu = document.querySelector('.menu')

burger.addEventListener('click', () => {
    menu.classList.toggle('hidden')
})
