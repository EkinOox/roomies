<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250619172748 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE friendship (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, friend_id INT NOT NULL, status VARCHAR(20) NOT NULL, INDEX IDX_7234A45FA76ED395 (user_id), INDEX IDX_7234A45F6A5458E8 (friend_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE friendship ADD CONSTRAINT FK_7234A45FA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE friendship ADD CONSTRAINT FK_7234A45F6A5458E8 FOREIGN KEY (friend_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_friends DROP FOREIGN KEY FK_79E36E636A5458E8
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_friends DROP FOREIGN KEY FK_79E36E63A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_friends
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE user_friends (user_id INT NOT NULL, friend_id INT NOT NULL, INDEX IDX_79E36E636A5458E8 (friend_id), INDEX IDX_79E36E63A76ED395 (user_id), PRIMARY KEY(user_id, friend_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = '' 
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_friends ADD CONSTRAINT FK_79E36E636A5458E8 FOREIGN KEY (friend_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_friends ADD CONSTRAINT FK_79E36E63A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE friendship DROP FOREIGN KEY FK_7234A45FA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE friendship DROP FOREIGN KEY FK_7234A45F6A5458E8
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE friendship
        SQL);
    }
}
