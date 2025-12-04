/**
 * =====================================================
 * PÁGINA MINHA CONTA - IZI STYLE E-COMMERCE
 * =====================================================
 * Gerencia área do usuário logado
 * Arquivo: js/conta.js
 * =====================================================
 */

// Executar quando o DOM carregar
document.addEventListener('DOMContentLoaded', function() {
    verificarAutenticacao();
});

/**
 * Verifica se usuário está autenticado
 */
async function verificarAutenticacao() {
    try {
        const response = await fetch('/api/verificar-sessao.php');
        const data = await response.json();
        
        if (!data.sucesso || !data.logado) {
            // Não está logado - redirecionar
            window.location.href = '/auth/login.html';
            return;
        }
        
        // Está logado - carregar dados
        await carregarDadosUsuario();
        
        // Esconder loading, mostrar conteúdo
        document.getElementById('loading-page').style.display = 'none';
        document.getElementById('conta-content').style.display = 'block';
        
        // Inicializar página
        inicializarPagina();
        
    } catch (error) {
        console.error('Erro ao verificar autenticação:', error);
        window.location.href = '/auth/login.html';
    }
}

/**
 * Carrega dados completos do usuário
 */
async function carregarDadosUsuario() {
    try {
        console.log('1. Fazendo requisição...');
        const response = await fetch('/api/obter-dados-usuario.php');
        
        console.log('2. Status:', response.status, response.statusText);
        
        // Verificar se resposta é OK
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        console.log('3. Lendo texto da resposta...');
        const textoResposta = await response.text();
        console.log('4. Texto recebido:', textoResposta.substring(0, 200) + '...');
        
        console.log('5. Parseando JSON...');
        let data;
        try {
            data = JSON.parse(textoResposta);
        } catch (erroJSON) {
            console.error('ERRO ao parsear JSON:', erroJSON);
            console.error('Texto que causou erro:', textoResposta);
            throw new Error('Resposta inválida do servidor');
        }
        
        console.log('6. Dados parseados:', data);
        console.log('7. data.sucesso:', data.sucesso);
        console.log('8. data.usuario:', data.usuario);
        console.log('9. typeof data.usuario:', typeof data.usuario);
        
        if (!data.sucesso) {
            throw new Error(data.mensagem || 'Erro ao carregar dados');
        }
        
        // Verificar se usuario existe - VÁRIAS FORMAS
        const usuario = data.usuario || data.dados?.usuario || data;
        
        console.log('10. Usuário final:', usuario);
        console.log('11. usuario.nome_completo:', usuario.nome_completo);
        
        if (!usuario || typeof usuario !== 'object') {
            console.error('ERRO: usuario não é um objeto!');
            console.error('Tipo:', typeof usuario);
            console.error('Valor:', usuario);
            throw new Error('Dados do usuário inválidos');
        }
        
        if (!usuario.nome_completo || !usuario.email) {
            console.error('ERRO: Campos obrigatórios faltando!');
            console.error('nome_completo:', usuario.nome_completo);
            console.error('email:', usuario.email);
            throw new Error('Dados do usuário incompletos');
        }
        
        console.log('12. Preenchendo sidebar...');
        
        // Preencher sidebar
        document.getElementById('user-name-sidebar').textContent = usuario.nome_completo;
        document.getElementById('user-email-sidebar').textContent = usuario.email;
        
        // Preencher formulário de dados pessoais
        document.getElementById('nome_completo').value = usuario.nome_completo || '';
        document.getElementById('email').value = usuario.email || '';
        document.getElementById('telefone').value = formatarTelefone(usuario.telefone || '');
        document.getElementById('cpf').value = formatarCPF(usuario.cpf || '');
        
        // Informações read-only
        document.getElementById('data-cadastro').textContent = formatarDataBR(usuario.data_cadastro);
        document.getElementById('ultimo-acesso').textContent = usuario.ultimo_acesso ? 
            formatarDataHoraBR(usuario.ultimo_acesso) : 'Primeiro acesso';
        
        // Preencher endereço
        document.getElementById('cep').value = formatarCEP(usuario.cep || '');
        document.getElementById('endereco').value = usuario.endereco || '';
        document.getElementById('numero').value = usuario.numero || '';
        document.getElementById('complemento').value = usuario.complemento || '';
        document.getElementById('bairro').value = usuario.bairro || '';
        document.getElementById('cidade').value = usuario.cidade || '';
        document.getElementById('estado').value = usuario.estado || '';
        
        // Preferências
        document.getElementById('aceita_newsletter').checked = usuario.aceita_newsletter === 't' || usuario.aceita_newsletter === true;
        
        // Verificação de email
        mostrarStatusEmail(usuario.email_verificado);
        
    } catch (error) {
        console.error('Erro ao carregar dados:', error);
        mostrarAlerta('Erro ao carregar seus dados. Recarregue a página.', 'error');
    }
}

/**
 * Mostra status de verificação de email
 */
function mostrarStatusEmail(emailVerificado) {
    const badge = document.getElementById('email-badge');
    const alert = document.getElementById('email-verification-alert');
    
    const verificado = emailVerificado === 't' || emailVerificado === true || emailVerificado === '1';
    
    if (verificado) {
        badge.textContent = '✓ Verificado';
        badge.className = 'email-badge verified';
        badge.style.display = 'inline-block';
        alert.style.display = 'none';
    } else {
        badge.textContent = '! Não Verificado';
        badge.className = 'email-badge not-verified';
        badge.style.display = 'inline-block';
        alert.style.display = 'block';
    }
}

/**
 * Inicializa eventos da página
 */
function inicializarPagina() {
    // Menu lateral
    configurarMenuLateral();
    
    // Formulários
    configurarFormularioDadosPessoais();
    configurarFormularioEndereco();
    configurarFormularioSenha();
    configurarFormularioPreferencias();
    
    // Botões
    configurarBotoes();
    
    // Toggle de senha
    configurarToggleSenha();
    
    // Formatação automática
    configurarFormatacaoAutomatica();
    
    // Busca CEP
    configurarBuscaCEP();
}

/**
 * Configura navegação do menu lateral
 */
function configurarMenuLateral() {
    const menuItems = document.querySelectorAll('.conta-menu-item[data-section]');
    
    menuItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            const sectionId = this.getAttribute('data-section');
            
            // Remover classe active de todos
            menuItems.forEach(mi => mi.classList.remove('active'));
            document.querySelectorAll('.conta-section-content').forEach(sec => sec.classList.remove('active'));
            
            // Adicionar classe active ao clicado
            this.classList.add('active');
            document.getElementById('section-' + sectionId).classList.add('active');
            
            // Scroll para o topo
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
}

/**
 * Configura formulário de dados pessoais
 */
function configurarFormularioDadosPessoais() {
    const form = document.getElementById('form-dados-pessoais');
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const dados = {
            nome_completo: document.getElementById('nome_completo').value,
            email: document.getElementById('email').value,
            telefone: document.getElementById('telefone').value.replace(/\D/g, ''),
            cpf: document.getElementById('cpf').value.replace(/\D/g, '')
        };
        
        // Validações
        if (!validarEmail(dados.email)) {
            mostrarAlerta('E-mail inválido.', 'error');
            return;
        }
        
        if (dados.cpf && !validarCPF(dados.cpf)) {
            mostrarAlerta('CPF inválido.', 'error');
            return;
        }
        
        if (!validarTelefone(dados.telefone)) {
            mostrarAlerta('Telefone inválido.', 'error');
            return;
        }
        
        // Enviar
        await salvarDados('/api/atualizar-dados.php', dados);
    });
}

/**
 * Configura formulário de endereço
 */
function configurarFormularioEndereco() {
    const form = document.getElementById('form-endereco');
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const dados = {
            cep: document.getElementById('cep').value.replace(/\D/g, ''),
            endereco: document.getElementById('endereco').value,
            numero: document.getElementById('numero').value,
            complemento: document.getElementById('complemento').value,
            bairro: document.getElementById('bairro').value,
            cidade: document.getElementById('cidade').value,
            estado: document.getElementById('estado').value.toUpperCase()
        };
        
        // Enviar
        await salvarDados('/api/atualizar-endereco.php', dados);
    });
}

/**
 * Configura formulário de alterar senha
 */
function configurarFormularioSenha() {
    const form = document.getElementById('form-alterar-senha');
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const senhaAtual = document.getElementById('senha_atual').value;
        const novaSenha = document.getElementById('nova_senha').value;
        const confirmarSenha = document.getElementById('confirmar_nova_senha').value;
        
        // Validações
        if (novaSenha.length < 6) {
            mostrarAlerta('A nova senha deve ter no mínimo 6 caracteres.', 'error');
            return;
        }
        
        if (novaSenha !== confirmarSenha) {
            mostrarAlerta('As senhas não coincidem.', 'error');
            return;
        }
        
        const dados = {
            senha_atual: senhaAtual,
            nova_senha: novaSenha
        };
        
        // Enviar
        const sucesso = await salvarDados('/api/alterar-senha-logado.php', dados);
        
        if (sucesso) {
            // Limpar campos
            form.reset();
        }
    });
}

/**
 * Configura formulário de preferências
 */
function configurarFormularioPreferencias() {
    const form = document.getElementById('form-preferencias');
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const dados = {
            aceita_newsletter: document.getElementById('aceita_newsletter').checked
        };
        
        // Enviar
        await salvarDados('/api/atualizar-preferencias.php', dados);
    });
}

/**
 * Função genérica para salvar dados
 */
async function salvarDados(url, dados) {
    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(dados)
        });
        
        const result = await response.json();
        
        if (result.sucesso) {
            mostrarAlerta(result.mensagem, 'success');
            
            // Se alterou email, recarregar status de verificação
            if (dados.email) {
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            }
            
            return true;
        } else {
            mostrarAlerta(result.mensagem, 'error');
            return false;
        }
    } catch (error) {
        console.error('Erro ao salvar:', error);
        mostrarAlerta('Erro ao salvar dados. Tente novamente.', 'error');
        return false;
    }
}

/**
 * Configura botões especiais
 */
function configurarBotoes() {
    // Botão de logout
    document.getElementById('btn-sair').addEventListener('click', async function(e) {
        e.preventDefault();
        
        try {
            await fetch('/api/logout.php', { method: 'POST' });
            window.location.href = '/index.html';
        } catch (error) {
            window.location.href = '/index.html';
        }
    });
    
    // Botão reenviar verificação
    document.getElementById('btn-resend-verification').addEventListener('click', async function() {
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
        
        try {
            const response = await fetch('/api/reenviar-verificacao.php', {
                method: 'POST'
            });
            
            const data = await response.json();
            
            if (data.sucesso) {
                mostrarAlerta('Email de verificação enviado! Verifique sua caixa de entrada.', 'success');
            } else {
                mostrarAlerta(data.mensagem, 'error');
            }
        } catch (error) {
            mostrarAlerta('Erro ao enviar email. Tente novamente.', 'error');
        } finally {
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-envelope"></i> Reenviar Email';
        }
    });
}

/**
 * Configura toggle de mostrar/esconder senha
 */
function configurarToggleSenha() {
    const toggleButtons = document.querySelectorAll('.toggle-password');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });
}

/**
 * Configura formatação automática de campos
 */
function configurarFormatacaoAutomatica() {
    // CPF
    const cpfInput = document.getElementById('cpf');
    if (cpfInput) {
        cpfInput.addEventListener('input', function() {
            this.value = formatarCPF(this.value);
        });
    }
    
    // Telefone
    const telefoneInput = document.getElementById('telefone');
    if (telefoneInput) {
        telefoneInput.addEventListener('input', function() {
            this.value = formatarTelefone(this.value);
        });
    }
    
    // CEP
    const cepInput = document.getElementById('cep');
    if (cepInput) {
        cepInput.addEventListener('input', function() {
            this.value = formatarCEP(this.value);
        });
    }
}

/**
 * Configura busca de CEP na API dos Correios
 */
function configurarBuscaCEP() {
    const cepInput = document.getElementById('cep');
    
    cepInput.addEventListener('blur', async function() {
        const cep = this.value.replace(/\D/g, '');
        
        if (cep.length !== 8) return;
        
        try {
            const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            const data = await response.json();
            
            if (!data.erro) {
                document.getElementById('endereco').value = data.logradouro;
                document.getElementById('bairro').value = data.bairro;
                document.getElementById('cidade').value = data.localidade;
                document.getElementById('estado').value = data.uf;
                
                // Focar no número
                document.getElementById('numero').focus();
            }
        } catch (error) {
            console.error('Erro ao buscar CEP:', error);
        }
    });
}

/**
 * Funções de formatação
 */
function formatarCPF(cpf) {
    cpf = cpf.replace(/\D/g, '');
    cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2');
    cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2');
    cpf = cpf.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    return cpf;
}

function formatarTelefone(telefone) {
    telefone = telefone.replace(/\D/g, '');
    if (telefone.length === 11) {
        telefone = telefone.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
    } else if (telefone.length === 10) {
        telefone = telefone.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
    }
    return telefone;
}

function formatarCEP(cep) {
    cep = cep.replace(/\D/g, '');
    cep = cep.replace(/(\d{5})(\d)/, '$1-$2');
    return cep;
}

function formatarDataBR(data) {
    if (!data) return '-';
    const d = new Date(data);
    return d.toLocaleDateString('pt-BR');
}

function formatarDataHoraBR(data) {
    if (!data) return '-';
    const d = new Date(data);
    return d.toLocaleString('pt-BR');
}

/**
 * Funções de validação (importadas de auth.js)
 */
function validarEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

function validarCPF(cpf) {
    cpf = cpf.replace(/\D/g, '');
    
    if (cpf.length !== 11) return false;
    if (/^(\d)\1+$/.test(cpf)) return false;
    
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

function validarTelefone(telefone) {
    telefone = telefone.replace(/\D/g, '');
    return telefone.length >= 10 && telefone.length <= 11;
}

/**
 * Mostra mensagem de alerta
 */
function mostrarAlerta(mensagem, tipo = 'info') {
    const alertDiv = document.getElementById('mensagem-alerta');
    
    alertDiv.className = `alert alert-${tipo}`;
    alertDiv.innerHTML = `
        <i class="fas fa-${tipo === 'success' ? 'check-circle' : tipo === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <span>${mensagem}</span>
    `;
    alertDiv.style.display = 'flex';
    
    // Scroll para o alerta
    alertDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    
    // Esconder após 5 segundos
    setTimeout(() => {
        alertDiv.style.display = 'none';
    }, 5000);
}