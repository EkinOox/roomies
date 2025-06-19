<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250618154835 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE game (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, image VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_game (user_id INT NOT NULL, game_id INT NOT NULL, INDEX IDX_59AA7D45A76ED395 (user_id), INDEX IDX_59AA7D45E48FD905 (game_id), PRIMARY KEY(user_id, game_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_user (user_source INT NOT NULL, user_target INT NOT NULL, INDEX IDX_F7129A803AD8644E (user_source), INDEX IDX_F7129A80233D34C1 (user_target), PRIMARY KEY(user_source, user_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_game ADD CONSTRAINT FK_59AA7D45A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_game ADD CONSTRAINT FK_59AA7D45E48FD905 FOREIGN KEY (game_id) REFERENCES game (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_user ADD CONSTRAINT FK_F7129A803AD8644E FOREIGN KEY (user_source) REFERENCES `user` (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_user ADD CONSTRAINT FK_F7129A80233D34C1 FOREIGN KEY (user_target) REFERENCES `user` (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE room ADD game_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE room ADD CONSTRAINT FK_729F519BE48FD905 FOREIGN KEY (game_id) REFERENCES game (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_729F519BE48FD905 ON room (game_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD avatar VARCHAR(255) NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE room DROP FOREIGN KEY FK_729F519BE48FD905
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_game DROP FOREIGN KEY FK_59AA7D45A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_game DROP FOREIGN KEY FK_59AA7D45E48FD905
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_user DROP FOREIGN KEY FK_F7129A803AD8644E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_user DROP FOREIGN KEY FK_F7129A80233D34C1
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE game
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_game
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_user
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_729F519BE48FD905 ON room
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE room DROP game_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `user` DROP avatar
        SQL);
    }
}
