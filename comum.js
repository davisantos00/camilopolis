// comum.js - Comportamentos compartilhados por todas as páginas
document.addEventListener('DOMContentLoaded', function () {

    // ---------- Avisos: fecham sozinhos depois de alguns segundos ----------
    document.querySelectorAll('.aviso-toast').forEach(function (aviso) {
        function fechar() {
            aviso.classList.add('aviso-saindo');
            setTimeout(function () { aviso.remove(); }, 300);
        }
        aviso.querySelector('.aviso-fechar').addEventListener('click', fechar);
        setTimeout(fechar, 6000);
    });

    // ---------- Botão de mostrar/ocultar em todos os campos de senha ----------
    document.querySelectorAll('input[type="password"]').forEach(function (campo) {
        var envoltorio = document.createElement('span');
        envoltorio.className = 'campo-senha';
        campo.parentNode.insertBefore(envoltorio, campo);
        envoltorio.appendChild(campo);

        var botao = document.createElement('button');
        botao.type = 'button';
        botao.className = 'btn-olho';
        botao.textContent = '👁';
        botao.setAttribute('aria-label', 'Mostrar senha');
        envoltorio.appendChild(botao);

        botao.addEventListener('click', function () {
            var mostrar = campo.type === 'password';
            campo.type = mostrar ? 'text' : 'password';
            botao.textContent = mostrar ? '🙈' : '👁';
            botao.setAttribute('aria-label', mostrar ? 'Ocultar senha' : 'Mostrar senha');
        });
    });
});
