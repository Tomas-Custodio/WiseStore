# 🛍️ WiseStore

WiseStore é uma loja virtual desenvolvida com **Laravel**, **Livewire**, **PHP** e **MySQL**.

Inclui gestão de produtos e categorias, carrinho de compras, encomendas e uma área administrativa para gerir a loja.

---

## ✨ Funcionalidades

- 📦 Gestão de produtos
- 🗂️ Gestão de categorias
- 🛒 Carrinho de compras
- 🧾 Encomendas
- 🔐 Área administrativa para gerir a loja

## 🛠️ Tecnologias

<p>
  <img src="https://skillicons.dev/icons?i=php,laravel,mysql,html,css,tailwind,js" alt="Tecnologias" />
</p>

- **Back-end:** PHP, Laravel, Livewire
- **Base de dados:** MySQL
- **Front-end:** Blade, Tailwind CSS

## 🖼️ Screenshots

<!-- Adiciona aqui prints da loja e da área administrativa:
![Página inicial](docs/home.png)
![Área administrativa](docs/admin.png)
-->

## ⚙️ Como executar o projeto

```bash
# 1. Clonar o repositório
git clone https://github.com/Tomas-Custodio/WiseStore.git
cd WiseStore

# 2. Instalar as dependências
composer install
npm install

# 3. Configurar o ambiente
cp .env.example .env
php artisan key:generate
```

Depois, configura a ligação à base de dados MySQL no ficheiro `.env`:

```env
DB_DATABASE=wisestore
DB_USERNAME=root
DB_PASSWORD=
```

```bash
# 4. Criar as tabelas
php artisan migrate

# 5. Iniciar o projeto
npm run dev
php artisan serve
```

Abre `http://localhost:8000` no navegador.

## 👤 Autor

**Tomás Custódio**
PHP & Laravel Developer | Full-Stack Web Developer

[![LinkedIn](https://img.shields.io/badge/LinkedIn-0A66C2?style=flat&logo=linkedin&logoColor=white)](https://www.linkedin.com/in/tomascustodio17)
[![GitHub](https://img.shields.io/badge/GitHub-181717?style=flat&logo=github&logoColor=white)](https://github.com/Tomas-Custodio)
