const RegisterBtn=document.getElementById('RegisterBtn');
const LoginBtn=document.getElementById('LoginBtn');
const LoginForm=document.getElementById('Login');
const RegisterForm=document.getElementById('Register');

RegisterBtn.addEventListener('click', function(){
    LoginForm.style.display="none";
    RegisterForm.style.display="block";
})
LoginBtn.addEventListener('click', function(){
    LoginForm.style.display="block";
    RegisterForm.style.display="none";
})

function project(){
    window.location.href="message.html";
}

function aboutPage(){
    window.location.href="AboutPage.html";
}

function logout() {
    window.location.href = "login.html";
}

function office_sel() {
    window.location.href = "office_selection.html";
}
