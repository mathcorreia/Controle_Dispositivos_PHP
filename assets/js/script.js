document.addEventListener('DOMContentLoaded', () => {
    const cadastramentoForm = document.getElementById('cadastramentoForm');
    if (cadastramentoForm) {
        cadastramentoForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(cadastramentoForm);
            
            try {
                const response = await fetch('/api/cadastramento.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (response.ok) {
                    alert(result.success);
                    cadastramentoForm.reset();
                    document.getElementById('id').value = ''; // Limpa o campo ID oculto
                    loadDispositivos();
                } else {
                    alert('Erro: ' + result.error);
                }
            } catch (error) {
                console.error('Falha na requisição:', error);
                alert('Ocorreu um erro de comunicação com o servidor.');
            }
        });
        loadDispositivos();
    }

    const movimentacaoForm = document.getElementById('movimentacaoForm');
    if (movimentacaoForm) {
        movimentacaoForm.addEventListener('submit', handleSimpleFormSubmit('/api/movimentacao.php', movimentacaoForm));
    }

    const manutencaoForm = document.getElementById('manutencaoForm');
    if (manutencaoForm) {
        manutencaoForm.addEventListener('submit', handleSimpleFormSubmit('/api/manutencao.php', manutencaoForm));
    }
});

function handleSimpleFormSubmit(url, formElement) {
    return async function(e) {
        e.preventDefault();
        const formData = new FormData(formElement);
        try {
            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            if (response.ok) {
                alert(result.success);
                formElement.reset();
            } else {
                alert('Erro: ' + result.error);
            }
        } catch (error) {
            console.error('Falha na requisição:', error);
            alert('Ocorreu um erro de comunicação com o servidor.');
        }
    }
}

async function loadDispositivos() {
    try {
        const response = await fetch('/api/cadastramento.php');
        const dispositivos = await response.json();
        
        if (dispositivos.error) {
             alert('Erro ao carregar dispositivos: ' + dispositivos.error);
             return;
        }
        
        const tableBody = document.querySelector('#dispositivosTable tbody');
        tableBody.innerHTML = '';
        for (const dispositivo of dispositivos) {
            const row = `<tr>
                <td>${dispositivo.id}</td>
                <td>${dispositivo.numero_dispositivo}</td>
                <td>${dispositivo.tipo_material}</td>
                <td>${dispositivo.localizacao}</td>
                <td>${dispositivo.status}</td>
                <td>
                    <button onclick="editDispositivo(${dispositivo.id})">Editar</button> 
                    <button onclick="deleteDispositivo(${dispositivo.id})">Excluir</button>
                </td>
            </tr>`;
            tableBody.innerHTML += row;
        }
    } catch (error) {
        console.error('Falha ao carregar dispositivos:', error);
    }
}

async function editDispositivo(id) {
    try {
        const response = await fetch(`/api/cadastramento.php?id=${id}`);
        const dispositivo = await response.json();

        if (dispositivo.error) {
            alert('Erro ao buscar dados do dispositivo: ' + dispositivo.error);
            return;
        }
        
        for (const key in dispositivo) {
            if (document.getElementById(key)) {
                document.getElementById(key).value = dispositivo[key];
            }
        }
        window.scrollTo(0, 0); 
    } catch (error) {
        console.error('Falha ao editar:', error);
    }
}

async function deleteDispositivo(id) {
    if (!confirm('Tem certeza que deseja excluir este dispositivo?')) {
        return;
    }

    try {
        const response = await fetch(`/api/cadastramento.php`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${id}`
        });
        const result = await response.json();

        if (response.ok) {
            alert(result.success);
            loadDispositivos();
        } else {
            alert('Erro: ' + result.error);
        }
    } catch (error) {
        console.error('Falha ao excluir:', error);
    }
}

const consultaLocalForm = document.getElementById('consultaLocalForm');
if (consultaLocalForm) {
    consultaLocalForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const local = document.getElementById('localizacao').value;
        const response = await fetch(`/api/consulta.php?tipo=local&termo=${encodeURIComponent(local)}`);
        const resultados = await response.json();
        
        const tableBody = document.querySelector('#resultadoLocal tbody');
        tableBody.innerHTML = ''; // Limpa resultados anteriores

        if (resultados && resultados.length > 0) {
            resultados.forEach(item => {
                const row = `<tr>
                    <td>${item.id}</td>
                    <td>${item.numero_dispositivo}</td>
                    <td>${item.tipo_material}</td>
                    <td>${item.operacao}</td>
                    <td>${item.setor}</td>
                    <td>${item.status}</td>
                </tr>`;
                tableBody.innerHTML += row;
            });
        } else {
            tableBody.innerHTML = '<tr><td colspan="6">Nenhum dispositivo encontrado para esta localização.</td></tr>';
        }
    });
}

// Página de Consulta de Acessórios
const consultaAcessoriosForm = document.getElementById('consultaAcessoriosForm');
if (consultaAcessoriosForm) {
    consultaAcessoriosForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const dispositivo = document.getElementById('numero_dispositivo').value;
        const response = await fetch(`/api/consulta.php?tipo=acessorios&termo=${encodeURIComponent(dispositivo)}`);
        const acessorios = await response.json();

        const divResultado = document.getElementById('resultadoAcessorios');
        divResultado.innerHTML = ''; // Limpa resultados

        if (acessorios) {
            let html = '<h3>Parafusos</h3><ul>';
            for (let i = 1; i <= 3; i++) {
                if (acessorios[`parafusos_${i}`]) {
                    html += `<li><b>Tipo ${i}:</b> ${acessorios[`parafusos_${i}`]} | <b>Qtd:</b> ${acessorios[`quantidade_parafuso_${i}`]} | <b>Rosca:</b> ${acessorios[`comprimento_rosca_${i}`]}</li>`;
                }
            }
            html += '</ul><h3>Pinos</h3><ul>';
            for (let i = 1; i <= 2; i++) {
                 if (acessorios[`pinos_${i}`]) {
                    html += `<li><b>Tipo ${i}:</b> ${acessorios[`pinos_${i}`]} | <b>Qtd:</b> ${acessorios[`quantidade_pino_${i}`]} | <b>Compr:</b> ${acessorios[`compr_pino_${i}`]}</li>`;
                }
            }
            html += '</ul><h3>Porcas</h3><ul>';
             for (let i = 1; i <= 2; i++) {
                 if (acessorios[`porca_t_${i}`]) {
                    html += `<li><b>Tipo ${i}:</b> ${acessorios[`porca_t_${i}`]} | <b>Qtd:</b> ${acessorios[`quantidade_porca_t_${i}`]}</li>`;
                }
            }
            html += '</ul>';
            divResultado.innerHTML = html;
        } else {
            divResultado.innerHTML = '<p>Nenhum dispositivo encontrado com este número ou não há acessórios cadastrados.</p>';
        }
    });
}