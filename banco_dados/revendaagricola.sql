DROP TABLE carrinho;
CREATE TABLE carrinho (id int NOT NULL AUTO_INCREMENT, usuario_id int NOT NULL, produto_id int NOT NULL, quantidade int NOT NULL, data_adicao timestamp DEFAULT current_timestamp(), PRIMARY KEY (id), INDEX usuario_id (usuario_id), INDEX produto_id (produto_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_general_ci;
DROP TABLE categorias;
CREATE TABLE categorias (id int NOT NULL AUTO_INCREMENT, nome varchar(100) NOT NULL, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_general_ci;
DROP TABLE imagens_produtos;
CREATE TABLE imagens_produtos (id int NOT NULL AUTO_INCREMENT, produto_id int NOT NULL, caminho varchar(255) NOT NULL, PRIMARY KEY (id), INDEX produto_id (produto_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_general_ci;
DROP TABLE imoveis;
CREATE TABLE imoveis (id int NOT NULL AUTO_INCREMENT, usuario_id int NOT NULL, titulo varchar(100) NOT NULL, descricao text, endereco varchar(255), cidade varchar(100), estado varchar(50), cep varchar(20), data_cadastro timestamp DEFAULT current_timestamp(), PRIMARY KEY (id), INDEX usuario_id (usuario_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_general_ci;
DROP TABLE produtos;
CREATE TABLE produtos (id int NOT NULL AUTO_INCREMENT, usuario_id int NOT NULL, categoria_id int NOT NULL, nome varchar(100) NOT NULL, descricao text, preco decimal(10,2) NOT NULL, quantidade int NOT NULL, data_cadastro timestamp DEFAULT current_timestamp(), PRIMARY KEY (id), INDEX usuario_id (usuario_id), INDEX categoria_id (categoria_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_general_ci;
DROP TABLE usuarios;
CREATE TABLE usuarios (id int NOT NULL AUTO_INCREMENT, nome varchar(100) NOT NULL, email varchar(100) NOT NULL, telefone varchar(20), senha varchar(255) NOT NULL, data_cadastro timestamp DEFAULT current_timestamp(), PRIMARY KEY (id), CONSTRAINT email UNIQUE (email)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_general_ci;
ALTER TABLE `carrinho` ADD FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ;
ALTER TABLE `carrinho` ADD FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`);
ALTER TABLE `imagens_produtos` ADD FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE;
ALTER TABLE `imoveis` ADD FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);
ALTER TABLE `produtos` ADD FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ;
ALTER TABLE `produtos` ADD FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);