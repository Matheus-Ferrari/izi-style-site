/**
 * =====================================================
 * JAVASCRIPT DE AUTENTICAÇÃO - IZI STYLE E-COMMERCE
 * =====================================================
 * Validações, AJAX e interações das páginas de login,
 * cadastro, recuperar senha e alterar senha
 * =====================================================
 */

// =====================================================
// UTILITÁRIOS
// =====================================================

/**
 * Mostra mensagem de alerta
 */
function mostrarAlerta(mensagem, tipo = 'error') {
    const alertaDiv = document.getElementById('mensagem-alerta');
    if (!alertaDiv) return;

    // Ícones por tipo
    const icones = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };

    alertaDiv.className = `alert alert-${tipo}`;
    alertaDiv.innerHTML = `
        <i class="fas ${icones[tipo]}"></i>
        <span>${mensagem}</span>
    `;
    alertaDiv.style.display = 'flex';

    // Auto-ocultar após 5 segundos
    setTimeout(() => {
        alertaDiv.style.display = 'none';
    }, 5000);

    // Scroll suave para o alerta
    alertaDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

/**
 * Esconde mensagem de alerta
 */
function esconderAlerta() {
    const alertaDiv = document.getElementById('mensagem-alerta');
    if (alertaDiv) {
        alertaDiv.style.display = 'none';
    }
}

/**
 * Valida formato de email
 */
function validarEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * Valida CPF
 */
function validarCPF(cpf) {
    cpf = cpf.replace(/[^\d]/g, '');
    
    if (cpf.length !== 11) return false;
    if (/^(\d)\1{10}$/.test(cpf)) return false;
    
    let soma = 0;
    for (let i = 0; i < 9; i++) {
        soma += parseInt(cpf.charAt(i)) * (10 - i);
    }
    let resto = 11 - (soma % 11);
    let digito1 = resto >= 10 ? 0 : resto;
    
    if (digito1 !== parseInt(cpf.charAt(9))) return false;
    
    soma = 0;
    for (let i = 0; i < 10; i++) {
        soma += parseInt(cpf.charAt(i)) * (11 - i);
    }
    resto = 11 - (soma % 11);
    let digito2 = resto >= 10 ? 0 : resto;
    
    return digito2 === parseInt(cpf.charAt(10));
}

/**
 * Formata CPF
 */
function formatarCPF(cpf) {
    cpf = cpf.replace(/[^\d]/g, '');
    return cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
}

/**
 * Formata telefone
 */
function formatarTelefone(telefone) {
    telefone = telefone.replace(/[^\d]/g, '');
    if (telefone.length === 11) {
        return telefone.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
    } else if (telefone.length === 10) {
        return telefone.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
    }
    return telefone;
}

/**
 * Desabilita botão durante carregamento
 */
function desabilitarBotao(botaoId) {
    const botao = document.getElementById(botaoId);
    if (!botao) return;
    
    botao.disabled = true;
    const textoOriginal = botao.querySelector('.btn-text');
    const textoLoading = botao.querySelector('.btn-loading');
    
    if (textoOriginal) textoOriginal.style.display = 'none';
    if (textoLoading) textoLoading.style.display = 'flex';
}

/**
 * Habilita botão após carregamento
 */
function habilitarBotao(botaoId) {
    const botao = document.getElementById(botaoId);
    if (!botao) return;
    
    botao.disabled = false;
    const textoOriginal = botao.querySelector('.btn-text');
    const textoLoading = botao.querySelector('.btn-loading');
    
    if (textoOriginal) textoOriginal.style.display = 'inline';
    if (textoLoading) textoLoading.style.display = 'none';
}

// =====================================================
// TOGGLE SENHA (MOSTRAR/ESCONDER)
// =====================================================

document.addEventListener('DOMContentLoaded', function() {
    const toggleButtons = document.querySelectorAll('.toggle-password');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                this.setAttribute('aria-label', 'Esconder senha');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                this.setAttribute('aria-label', 'Mostrar senha');
            }
        });
    });
});

// =====================================================
// FORMATAÇÃO AUTOMÁTICA DE CAMPOS
// =====================================================

document.addEventListener('DOMContentLoaded', function() {
    // CPF
    const campoCPF = document.getElementById('cpf');
    if (campoCPF) {
        campoCPF.addEventListener('input', function(e) {
            let valor = e.target.value.replace(/[^\d]/g, '');
            if (valor.length <= 11) {
                e.target.value = formatarCPF(valor);
            }
        });
    }
    
    // Telefone
    const campoTelefone = document.getElementById('telefone');
    if (campoTelefone) {
        campoTelefone.addEventListener('input', function(e) {
            let valor = e.target.value.replace(/[^\d]/g, '');
            if (valor.length <= 11) {
                e.target.value = formatarTelefone(valor);
            }
        });
    }
});

// =====================================================
// LOGIN
// =====================================================

const formLogin = document.getElementById('form-login');
if (formLogin) {
    formLogin.addEventListener('submit', async function(e) {
        e.preventDefault();
        esconderAlerta();
        
        const email = document.getElementById('email').value.trim();
        const senha = document.getElementById('senha').value;
        
        // Validações básicas
        if (!email || !senha) {
            mostrarAlerta('Por favor, preencha todos os campos.', 'warning');
            return;
        }
        
        if (!validarEmail(email)) {
            mostrarAlerta('Por favor, informe um e-mail válido.', 'warning');
            return;
        }
        
        if (senha.length < 6) {
            mostrarAlerta('A senha deve ter no mínimo 6 caracteres.', 'warning');
            return;
        }
        
        // Desabilitar botão
        desabilitarBotao('btn-login');
        
        try {
            const response = await fetch('../api/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email, senha })
            });
            
            const data = await response.json();
            
            if (data.sucesso) {
                mostrarAlerta('Login realizado com sucesso! Redirecionando...', 'success');
                
                // Redirecionar após 1.5 segundos
                setTimeout(() => {
                    window.location.href = '../index.html';
                }, 1500);
            } else {
                mostrarAlerta(data.mensagem || 'Erro ao fazer login. Tente novamente.', 'error');
                habilitarBotao('btn-login');
            }
        } catch (error) {
            console.error('Erro:', error);
            mostrarAlerta('Erro ao conectar com o servidor. Tente novamente.', 'error');
            habilitarBotao('btn-login');
        }
    });
}

// =====================================================
// CADASTRO
// =====================================================

const formCadastro = document.getElementById('form-cadastro');
if (formCadastro) {
    formCadastro.addEventListener('submit', async function(e) {
        e.preventDefault();
        esconderAlerta();
        
        const nomeCompleto = document.getElementById('nome_completo').value.trim();
        const email = document.getElementById('email').value.trim();
        const telefone = document.getElementById('telefone').value.trim();
        const cpf = document.getElementById('cpf') ? document.getElementById('cpf').value.trim() : '';
        const senha = document.getElementById('senha').value;
        const confirmarSenha = document.getElementById('confirmar_senha').value;
        const aceitaTermos = document.getElementById('aceita_termos') ? document.getElementById('aceita_termos').checked : false;
        
        // Validações
        if (!nomeCompleto || !email || !telefone || !senha || !confirmarSenha) {
            mostrarAlerta('Por favor, preencha todos os campos obrigatórios.', 'warning');
            return;
        }
        
        if (!validarEmail(email)) {
            mostrarAlerta('Por favor, informe um e-mail válido.', 'warning');
            return;
        }
        
        if (cpf && !validarCPF(cpf)) {
            mostrarAlerta('Por favor, informe um CPF válido.', 'warning');
            return;
        }
        
        if (senha.length < 6) {
            mostrarAlerta('A senha deve ter no mínimo 6 caracteres.', 'warning');
            return;
        }
        
        if (senha !== confirmarSenha) {
            mostrarAlerta('As senhas não coincidem.', 'warning');
            return;
        }
        
        if (!aceitaTermos) {
            mostrarAlerta('Você deve aceitar os termos de uso.', 'warning');
            return;
        }
        
        // Desabilitar botão
        desabilitarBotao('btn-cadastro');
        
        try {
            const response = await fetch('../api/cadastro.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    nome_completo: nomeCompleto,
                    email,
                    telefone: telefone.replace(/[^\d]/g, ''),
                    cpf: cpf.replace(/[^\d]/g, ''),
                    senha,
                    aceita_termos: aceitaTermos
                })
            });
            
            const data = await response.json();
            
            if (data.sucesso) {
                mostrarAlerta('Cadastro realizado com sucesso! Redirecionando para login...', 'success');
                
                // Redirecionar após 2 segundos
                setTimeout(() => {
                    window.location.href = 'login.html';
                }, 2000);
            } else {
                mostrarAlerta(data.mensagem || 'Erro ao criar conta. Tente novamente.', 'error');
                habilitarBotao('btn-cadastro');
            }
        } catch (error) {
            console.error('Erro:', error);
            mostrarAlerta('Erro ao conectar com o servidor. Tente novamente.', 'error');
            habilitarBotao('btn-cadastro');
        }
    });
}

// =====================================================
// RECUPERAR SENHA
// =====================================================

const formRecuperar = document.getElementById('form-recuperar');
if (formRecuperar) {
    formRecuperar.addEventListener('submit', async function(e) {
        e.preventDefault();
        esconderAlerta();
        
        const email = document.getElementById('email').value.trim();
        
        if (!email) {
            mostrarAlerta('Por favor, informe seu e-mail.', 'warning');
            return;
        }
        
        if (!validarEmail(email)) {
            mostrarAlerta('Por favor, informe um e-mail válido.', 'warning');
            return;
        }
        
        desabilitarBotao('btn-recuperar');
        
        try {
            const response = await fetch('../api/recuperar_senha.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email })
            });
            
            const data = await response.json();
            
            if (data.sucesso) {
                mostrarAlerta('Instruções enviadas para seu e-mail!', 'success');
                // Limpar formulário
                formRecuperar.reset();
            } else {
                mostrarAlerta(data.mensagem || 'Erro ao enviar instruções. Tente novamente.', 'error');
            }
            
            habilitarBotao('btn-recuperar');
        } catch (error) {
            console.error('Erro:', error);
            mostrarAlerta('Erro ao conectar com o servidor. Tente novamente.', 'error');
            habilitarBotao('btn-recuperar');
        }
    });
}

// =====================================================
// ALTERAR SENHA
// =====================================================

const formAlterar = document.getElementById('form-alterar');
if (formAlterar) {
    formAlterar.addEventListener('submit', async function(e) {
        e.preventDefault();
        esconderAlerta();
        
        const novaSenha = document.getElementById('nova_senha').value;
        const confirmarSenha = document.getElementById('confirmar_senha').value;
        
        // Pegar token da URL
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token');
        
        if (!token) {
            mostrarAlerta('Token inválido ou expirado.', 'error');
            return;
        }
        
        if (!novaSenha || !confirmarSenha) {
            mostrarAlerta('Por favor, preencha todos os campos.', 'warning');
            return;
        }
        
        if (novaSenha.length < 6) {
            mostrarAlerta('A senha deve ter no mínimo 6 caracteres.', 'warning');
            return;
        }
        
        if (novaSenha !== confirmarSenha) {
            mostrarAlerta('As senhas não coincidem.', 'warning');
            return;
        }
        
        desabilitarBotao('btn-alterar');
        
        try {
            const response = await fetch('../api/alterar_senha.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    token,
                    nova_senha: novaSenha
                })
            });
            
            const data = await response.json();
            
            if (data.sucesso) {
                mostrarAlerta('Senha alterada com sucesso! Redirecionando para login...', 'success');
                
                setTimeout(() => {
                    window.location.href = 'login.html';
                }, 2000);
            } else {
                mostrarAlerta(data.mensagem || 'Erro ao alterar senha. Tente novamente.', 'error');
                habilitarBotao('btn-alterar');
            }
        } catch (error) {
            console.error('Erro:', error);
            mostrarAlerta('Erro ao conectar com o servidor. Tente novamente.', 'error');
            habilitarBotao('btn-alterar');
        }
    });
}