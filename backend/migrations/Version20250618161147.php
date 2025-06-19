<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250618161147 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE user_friends (user_id INT NOT NULL, friend_id INT NOT NULL, INDEX IDX_79E36E63A76ED395 (user_id), INDEX IDX_79E36E636A5458E8 (friend_id), PRIMARY KEY(user_id, friend_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_friends ADD CONSTRAINT FK_79E36E63A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_friends ADD CONSTRAINT FK_79E36E636A5458E8 FOREIGN KEY (friend_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_user DROP FOREIGN KEY FK_F7129A80233D34C1
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_user DROP FOREIGN KEY FK_F7129A803AD8644E
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_user
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_8D93D649F85E0677 ON user (username)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE user_user (user_source INT NOT NULL, user_target INT NOT NULL, INDEX IDX_F7129A803AD8644E (user_source), INDEX IDX_F7129A80233D34C1 (user_target), PRIMARY KEY(user_source, user_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_user ADD CONSTRAINT FK_F7129A80233D34C1 FOREIGN KEY (user_target) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_user ADD CONSTRAINT FK_F7129A803AD8644E FOREIGN KEY (user_source) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_friends DROP FOREIGN KEY FK_79E36E63A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_friends DROP FOREIGN KEY FK_79E36E636A5458E8
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_friends
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_8D93D649F85E0677 ON `user`
        SQL);
    }
}
