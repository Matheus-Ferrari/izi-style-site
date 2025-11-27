// produtos.js - Script para carregar e exibir produtos

// ===== BANCO DE DADOS SIMULADO DE PRODUTOS =====
const produtosDatabase = [
    // CATEGORIA: CASAL
    {
        id: 1,
        nome: "Camiseta Casal - Love Forever",
        categoria: "casal",
        preco: 89.90,
        precoAntigo: 119.90,
        imagem: "imagens/casal/produto-01.jpg",
        badge: "oferta"
    },
    {
        id: 2,
        nome: "Kit Canecas Personalizadas Casal",
        categoria: "casal",
        preco: 69.90,
        imagem: "imagens/casal/produto-02.jpg"
    },
    {
        id: 3,
        nome: "Almofada Decorativa - Amor Eterno",
        categoria: "casal",
        preco: 54.90,
        imagem: "imagens/casal/produto-03.jpg",
        badge: "novo"
    },
    {
        id: 4,
        nome: "Quadro Personalizado - Nossa História",
        categoria: "casal",
        preco: 129.90,
        imagem: "imagens/casal/produto-04.jpg"
    },

    // CATEGORIA: FAMÍLIA
    {
        id: 5,
        nome: "Kit Camisetas Família Completa",
        categoria: "familia",
        preco: 199.90,
        precoAntigo: 249.90,
        imagem: "imagens/familia/produto-01.jpg",
        badge: "oferta"
    },
    {
        id: 6,
        nome: "Quadro Família Feliz",
        categoria: "familia",
        preco: 149.90,
        imagem: "imagens/familia/produto-02.jpg"
    },
    {
        id: 7,
        nome: "Almofadas Kit Família (4 unidades)",
        categoria: "familia",
        preco: 179.90,
        imagem: "imagens/familia/produto-03.jpg",
        badge: "novo"
    },
    {
        id: 8,
        nome: "Canecas Personalizadas Família",
        categoria: "familia",
        preco: 119.90,
        imagem: "imagens/familia/produto-04.jpg"
    },

    // CATEGORIA: AMIGOS
    {
        id: 9,
        nome: "Camisetas Squad - Melhores Amigos",
        categoria: "amigos",
        preco: 159.90,
        imagem: "imagens/amigos/produto-01.jpg"
    },
    {
        id: 10,
        nome: "Kit Canecas Amizade (6 unidades)",
        categoria: "amigos",
        preco: 139.90,
        precoAntigo: 179.90,
        imagem: "imagens/amigos/produto-02.jpg",
        badge: "oferta"
    },
    {
        id: 11,
        nome: "Quadro Decorativo - Friends Forever",
        categoria: "amigos",
        preco: 99.90,
        imagem: "imagens/amigos/produto-03.jpg"
    },

    // CATEGORIA: DIA DOS NAMORADOS
    {
        id: 12,
        nome: "Camiseta Especial Namorados 2025",
        categoria: "namorados",
        preco: 79.90,
        imagem: "imagens/namorados/produto-01.jpg",
        badge: "novo"
    },
    {
        id: 13,
        nome: "Kit Presente Romântico Completo",
        categoria: "namorados",
        preco: 189.90,
        precoAntigo: 239.90,
        imagem: "imagens/namorados/produto-02.jpg",
        badge: "oferta"
    },
    {
        id: 14,
        nome: "Canecas Personalizadas Love",
        categoria: "namorados",
        preco: 64.90,
        imagem: "imagens/namorados/produto-03.jpg"
    },

    // CATEGORIA: DIA DAS MÃES
    {
        id: 15,
        nome: "Camiseta Mãe Especial",
        categoria: "maes",
        preco: 69.90,
        imagem: "imagens/maes/produto-01.jpg",
        badge: "novo"
    },
    {
        id: 16,
        nome: "Kit Presente Dia das Mães",
        categoria: "maes",
        preco: 149.90,
        precoAntigo: 189.90,
        imagem: "imagens/maes/produto-02.jpg",
        badge: "oferta"
    },

    // CATEGORIA: DIA DOS PAIS
    {
        id: 17,
        nome: "Camiseta Pai Herói",
        categoria: "pais",
        preco: 69.90,
        imagem: "imagens/pais/produto-01.jpg"
    },
    {
        id: 18,
        nome: "Kit Presente Dia dos Pais",
        categoria: "pais",
        preco: 149.90,
        imagem: "imagens/pais/produto-02.jpg",
        badge: "novo"
    },

    // CATEGORIA: CARNAVAL
    {
        id: 19,
        nome: "Camiseta Carnaval 2025",
        categoria: "carnaval",
        preco: 59.90,
        imagem: "imagens/carnaval/produto-01.jpg",
        badge: "novo"
    },
    {
        id: 20,
        nome: "Kit Fantasia Personalizada",
        categoria: "carnaval",
        preco: 129.90,
        precoAntigo: 159.90,
        imagem: "imagens/carnaval/produto-02.jpg",
        badge: "oferta"
    },

    // CATEGORIA: DIA DOS PROFESSORES
    {
        id: 21,
        nome: "Camiseta Professor(a) Nota 10",
        categoria: "professores",
        preco: 64.90,
        imagem: "imagens/professores/produto-01.jpg"
    },
    {
        id: 22,
        nome: "Caneca Personalizada Professor(a)",
        categoria: "professores",
        preco: 39.90,
        imagem: "imagens/professores/produto-02.jpg",
        badge: "novo"
    }
];

// ===== MAPEAMENTO DE NOMES DE CATEGORIAS =====
const categoriasNomes = {
    'casal': 'Para Casal',
    'familia': 'Para Família',
    'amigos': 'Para Amigos',
    'namorados': 'Dia dos Namorados',
    'maes': 'Dia das Mães',
    'pais': 'Dia dos Pais',
    'carnaval': 'Carnaval',
    'professores': 'Dia dos Professores',
    'todos': 'Todos os Produtos'
};

// ===== FUNÇÕES PRINCIPAIS =====

/**
 * Obtém a categoria da URL
 * Exemplo: produtos.html?categoria=casal retorna "casal"
 */
function obterCategoriaURL() {
    const params = new URLSearchParams(window.location.search);
    return params.get('categoria') || 'todos';
}

/**
 * Filtra produtos por categoria
 */
function filtrarProdutos(categoria) {
    if (categoria === 'todos') {
        return produtosDatabase;
    }
    return produtosDatabase.filter(produto => produto.categoria === categoria);
}

/**
 * Formata preço para Real brasileiro
 */
function formatarPreco(preco) {
    return preco.toLocaleString('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    });
}

/**
 * Renderiza um card de produto
 */
function renderizarProdutoCard(produto) {
    const badgeHTML = produto.badge 
        ? `<span class="produto-badge ${produto.badge}">${produto.badge === 'novo' ? 'Novo' : 'Oferta'}</span>` 
        : '';
    
    const precoAntigoHTML = produto.precoAntigo 
        ? `<span class="produto-preco-antigo">${formatarPreco(produto.precoAntigo)}</span>` 
        : '';

    return `
        <article class="produto-card" data-produto-id="${produto.id}" tabindex="0">
            <div class="produto-imagem-container">
                ${badgeHTML}
                <img 
                    src="${produto.imagem}" 
                    alt="${produto.nome}" 
                    class="produto-imagem"
                    onerror="this.parentElement.innerHTML='<div class=\\'produto-imagem-placeholder\\'><i class=\\'fas fa-image\\'></i></div>'"
                />
            </div>
            <div class="produto-info">
                <span class="produto-categoria">${categoriasNomes[produto.categoria]}</span>
                <h3 class="produto-nome">${produto.nome}</h3>
                <div class="produto-preco">
                    ${formatarPreco(produto.preco)}
                    ${precoAntigoHTML}
                </div>
                <button class="btn-comprar" onclick="comprarProduto(${produto.id})">
                    <i class="fas fa-shopping-cart"></i> Comprar
                </button>
            </div>
        </article>
    `;
}

/**
 * Renderiza todos os produtos na tela
 */
function renderizarProdutos() {
    const categoria = obterCategoriaURL();
    const produtos = filtrarProdutos(categoria);
    const produtosGrid = document.getElementById('produtos-grid');
    const semProdutos = document.getElementById('sem-produtos');
    const tituloCategoria = document.getElementById('titulo-categoria');
    const categoriaAtual = document.getElementById('categoria-atual');

    // Atualiza título e breadcrumb
    const nomeCategoria = categoriasNomes[categoria] || 'Produtos';
    tituloCategoria.textContent = nomeCategoria;
    categoriaAtual.textContent = nomeCategoria;

    // Verifica se há produtos
    if (produtos.length === 0) {
        produtosGrid.style.display = 'none';
        semProdutos.style.display = 'block';
        return;
    }

    // Renderiza os produtos
    semProdutos.style.display = 'none';
    produtosGrid.style.display = 'grid';
    produtosGrid.innerHTML = produtos.map(produto => renderizarProdutoCard(produto)).join('');

    // Adiciona eventos de clique nos cards
    adicionarEventosCards();
}

/**
 * Adiciona eventos de clique nos cards
 */
function adicionarEventosCards() {
    const cards = document.querySelectorAll('.produto-card');
    
    cards.forEach(card => {
        // Clique no card (exceto no botão)
        card.addEventListener('click', (e) => {
            if (!e.target.closest('.btn-comprar')) {
                const produtoId = card.dataset.produtoId;
                visualizarProduto(produtoId);
            }
        });

        // Enter no card (acessibilidade)
        card.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.target.closest('.btn-comprar')) {
                const produtoId = card.dataset.produtoId;
                visualizarProduto(produtoId);
            }
        });
    });
}

/**
 * Visualiza detalhes do produto
 */
function visualizarProduto(produtoId) {
    // Aqui você pode redirecionar para uma página de detalhes
    // Por enquanto, vamos mostrar um alerta
    console.log('Visualizando produto:', produtoId);
    
    // Exemplo de redirecionamento (descomente quando tiver a página de detalhes):
    // window.location.href = `produto-detalhes.html?id=${produtoId}`;
    
    alert(`Visualizando produto ID: ${produtoId}\n\nEm breve você será redirecionado para a página de detalhes!`);
}

/**
 * Compra o produto (adiciona ao carrinho ou redireciona para checkout)
 */
function comprarProduto(produtoId) {
    const produto = produtosDatabase.find(p => p.id === produtoId);
    
    if (!produto) {
        alert('Produto não encontrado!');
        return;
    }

    console.log('Comprando produto:', produto);
    
    // Aqui você pode:
    // 1. Adicionar ao carrinho
    // 2. Redirecionar para WhatsApp
    // 3. Redirecionar para checkout
    
    // Exemplo: Redirecionar para WhatsApp com mensagem pré-preenchida
    const mensagem = `Olá! Gostaria de comprar:\n*${produto.nome}*\nPreço: ${formatarPreco(produto.preco)}`;
    const whatsappURL = `https://wa.me/5511976231926?text=${encodeURIComponent(mensagem)}`;
    
    // Pergunta ao usuário
    if (confirm(`Deseja comprar "${produto.nome}" por ${formatarPreco(produto.preco)}?\n\nVocê será redirecionado para o WhatsApp.`)) {
        window.open(whatsappURL, '_blank');
    }
}

// ===== INICIALIZAÇÃO =====
document.addEventListener('DOMContentLoaded', () => {
    renderizarProdutos();
    
    // Log para debug
    console.log('🛍️ Sistema de produtos carregado!');
    console.log('📦 Total de produtos:', produtosDatabase.length);
    console.log('📂 Categoria atual:', obterCategoriaURL());
});
