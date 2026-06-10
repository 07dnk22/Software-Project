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