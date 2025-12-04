/**
 * =====================================================
 * GERENCIAMENTO DE SESSÃO - IZI STYLE E-COMMERCE
 * =====================================================
 * Detecta usuário logado e atualiza interface do header
 * Arquivo: js/session.js
 * =====================================================
 */

// Executar quando o DOM carregar
document.addEventListener('DOMContentLoaded', function() {
    verificarSessao();
});

/**
 * Verifica se há sessão ativa do usuário
 */
async function verificarSessao() {
    try {
        // Fazer requisição para verificar sessão no servidor
        const response = await fetch('/api/verificar-sessao.php', {
            method: 'GET',
            credentials: 'include' // Importante para enviar cookies de sessão
        });
        
        const data = await response.json();
        
        if (data.sucesso && data.logado) {
            // Usuário está logado
            atualizarHeaderLogado(data.usuario);
        } else {
            // Usuário não está logado
            atualizarHeaderDeslogado();
        }
    } catch (error) {
        console.error('Erro ao verificar sessão:', error);
        // Em caso de erro, manter interface padrão
        atualizarHeaderDeslogado();
    }
}

/**
 * Atualiza header para usuário logado
 */
function atualizarHeaderLogado(usuario) {
    // Pegar elementos do header
    const btnMinhaConta = document.querySelector('a[href*="login.html"]');
    const btnCriarConta = document.querySelector('a[href*="cadastro.html"]');
    
    if (!btnMinhaConta || !btnCriarConta) return;
    
    // Pegar primeiro nome do usuário
    const primeiroNome = usuario.nome.split(' ')[0];
    
    // Criar dropdown do usuário
    const userDropdown = document.createElement('div');
    userDropdown.className = 'user-dropdown';
    userDropdown.innerHTML = `
        <button class="btn btn--user" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-user-circle"></i>
            <span>Olá, ${primeiroNome}</span>
            <i class="fas fa-chevron-down"></i>
        </button>
        <ul class="user-dropdown-menu" role="menu">
            <li>
                <a href="/auth/minha-conta.html">
                    <i class="fas fa-user"></i>
                    Meu Perfil
                </a>
            </li>
            <li>
                <a href="/pedidos.html">
                    <i class="fas fa-shopping-bag"></i>
                    Meus Pedidos
                </a>
            </li>
            <li>
                <a href="/favoritos.html">
                    <i class="fas fa-heart"></i>
                    Favoritos
                </a>
            </li>
            <li class="divider"></li>
            <li>
                <a href="#" id="btn-logout">
                    <i class="fas fa-sign-out-alt"></i>
                    Sair
                </a>
            </li>
        </ul>
    `;
    
    // Substituir botão "Minha conta" pelo dropdown
    btnMinhaConta.replaceWith(userDropdown);
    
    // Substituir botão "CRIAR SUA CONTA" por botão de logout direto
    btnCriarConta.outerHTML = `
        <a class="btn btn--ghost btn-logout-mobile" href="#" id="btn-logout-mobile">
            <i class="fas fa-sign-out-alt"></i>
            Sair
        </a>
    `;
    
    // Adicionar eventos de dropdown
    configurarDropdownUsuario();
    
    // Adicionar eventos de logout
    configurarLogout();
}

/**
 * Atualiza header para usuário deslogado (padrão)
 */
function atualizarHeaderDeslogado() {
    // Interface padrão já está no HTML
    // Apenas garantir que links estão corretos
    const btnMinhaConta = document.querySelector('a[href*="conta"]');
    const btnCriarConta = document.querySelector('a[href*="criar-conta"]');
    
    if (btnMinhaConta) {
        btnMinhaConta.setAttribute('href', 'auth/login.html');
    }
    
    if (btnCriarConta) {
        btnCriarConta.setAttribute('href', 'auth/cadastro.html');
    }
}

/**
 * Configura comportamento do dropdown do usuário
 */
function configurarDropdownUsuario() {
    const dropdownBtn = document.querySelector('.btn--user');
    const dropdownMenu = document.querySelector('.user-dropdown-menu');
    
    if (!dropdownBtn || !dropdownMenu) return;
    
    // Toggle dropdown ao clicar
    dropdownBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const isOpen = dropdownBtn.getAttribute('aria-expanded') === 'true';
        dropdownBtn.setAttribute('aria-expanded', !isOpen);
        
        // Toggle classe ativa
        const dropdown = dropdownBtn.closest('.user-dropdown');
        dropdown.classList.toggle('active');
    });
    
    // Fechar dropdown ao clicar fora
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.user-dropdown')) {
            const dropdown = document.querySelector('.user-dropdown');
            if (dropdown) {
                dropdown.classList.remove('active');
                const btn = dropdown.querySelector('.btn--user');
                if (btn) btn.setAttribute('aria-expanded', 'false');
            }
        }
    });
    
    // Fechar ao pressionar ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const dropdown = document.querySelector('.user-dropdown.active');
            if (dropdown) {
                dropdown.classList.remove('active');
                const btn = dropdown.querySelector('.btn--user');
                if (btn) btn.setAttribute('aria-expanded', 'false');
            }
        }
    });
}

/**
 * Configura eventos de logout
 */
function configurarLogout() {
    // Botão de logout no dropdown
    const btnLogout = document.getElementById('btn-logout');
    if (btnLogout) {
        btnLogout.addEventListener('click', fazerLogout);
    }
    
    // Botão de logout mobile
    const btnLogoutMobile = document.getElementById('btn-logout-mobile');
    if (btnLogoutMobile) {
        btnLogoutMobile.addEventListener('click', fazerLogout);
    }
}

/**
 * Faz logout do usuário
 */
async function fazerLogout(e) {
    if (e) e.preventDefault();
    
    try {
        const response = await fetch('/api/logout.php', {
            method: 'POST',
            credentials: 'include'
        });
        
        const data = await response.json();
        
        if (data.sucesso) {
            // Redirecionar para home
            window.location.href = '/index.html';
        } else {
            console.error('Erro ao fazer logout:', data.mensagem);
            // Mesmo assim redirecionar
            window.location.href = '/index.html';
        }
    } catch (error) {
        console.error('Erro ao fazer logout:', error);
        // Forçar reload da página
        window.location.href = '/index.html';
    }
}

/**
 * Salva dados do usuário no sessionStorage (para uso em outras páginas)
 */
function salvarDadosUsuario(usuario) {
    sessionStorage.setItem('usuario_logado', 'true');
    sessionStorage.setItem('usuario_id', usuario.id);
    sessionStorage.setItem('usuario_nome', usuario.nome);
    sessionStorage.setItem('usuario_email', usuario.email);
}

/**
 * Remove dados do usuário do sessionStorage
 */
function limparDadosUsuario() {
    sessionStorage.removeItem('usuario_logado');
    sessionStorage.removeItem('usuario_id');
    sessionStorage.removeItem('usuario_nome');
    sessionStorage.removeItem('usuario_email');
}