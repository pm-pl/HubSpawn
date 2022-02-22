<?php

/*
 *
 *  _____         _ _           _   _____
 * |  __ \       | | |         | | |  __ \
 * | |__) |__  __| | |__   ___ | |_| |  | | _____   __
 * |  ___/ _ \/ _` | '_ \ / _ \| __| |  | |/ _ \ \ / /
 * | |  |  __/ (_| | | | | (_) | |_| |__| |  __/\ V /
 * |_|   \___|\__,_|_| |_|\___/ \__|_____/ \___| \_/
 *
 *
 * Copyright 2021 Pedhot-Dev
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 *
 * @author PedhotDev
 * @link https://github.com/Pedhot-Dev/HubSpawn
 *
 */

namespace pedhot\hubspawn;

use JackMD\ConfigUpdater\ConfigUpdater;
use pedhot\hubspawn\command\HubCommand;
use pedhot\hubspawn\command\HubSpawnCommand;
use pedhot\hubspawn\command\SpawnCommand;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\world\Position;

class HubSpawn extends PluginBase
{

    /** @var Config */
    private static $data, $message;

    /** @var self */
    private static $instance;

    private $selectedLang;

    public static function getInstance(): self {
        return self::$instance;
    }

    protected function onLoad(): void {
        self::$instance = $this;

        $this->selectedLang = str_replace(["ID", "ENG"], ["indonesian", "english"], $this->getConfig()->get("language"));
    }

    protected function onEnable(): void {
        $this->saveDefaultConfig();
        $this->saveResource("message.yml");
        $this->saveResource("data.yml");
        $this->checkConfig();
        $this->registerAllCommands();

        self::$data = new Config($this->getDataFolder()."data.yml");
        self::$message = new Config($this->getDataFolder()."message.yml");
    }

    private function registerAllCommands(): void {
        Server::getInstance()->getCommandMap()->register($this->getName(), new HubSpawnCommand($this, $this->getConfig()->getNested("commands-settings.hubspawn.name")));
        Server::getInstance()->getCommandMap()->register($this->getName(), new HubCommand($this, $this->getConfig()->getNested("commands-settings.hub.name")));
        Server::getInstance()->getCommandMap()->register($this->getName(), new SpawnCommand($this, $this->getConfig()->getNested("commands-settings.spawn.name")));
    }

    private function checkConfig(): void {
        if (ConfigUpdater::checkUpdate($this, $this->getConfig(), "config-version", 1.0)) {
            $this->reloadConfig();
        }
        if (ConfigUpdater::checkUpdate($this, self::$message, "version-message", 1.0)) {
            self::$message->reload();
        }
    }

    /**
     * @return Config
     */
    public function getMessage(): Config {
        return self::$message;
    }

    /**
     * @return string
     */
    public function getSelectedLang(): string {
        return $this->selectedLang;
    }

    /**
     * @param string $type
     * @param Position $pos
     * @return void
     * @throws \JsonException
     */
    public static function set(string $type, Position $pos): void {
        if (!in_array($type, ["spawn", "hub"])) {
            return;
        }
        self::$data->setNested($type.".x", $pos->getX());
        self::$data->setNested($type.".y", $pos->getY());
        self::$data->setNested($type.".z", $pos->getZ());
        self::$data->setNested($type.".world-name", $pos->getWorld()->getFolderName());
        self::$data->save();
    }

    /**
     * @param string $type
     * @return void
     * @throws \JsonException
     */
    public static function reset(string $type): void {
        if (!in_array($type, ["spawn", "hub"])) {
            return;
        }
        self::$data->setNested($type.".x", 0);
        self::$data->setNested($type.".y", 0);
        self::$data->setNested($type.".z", 0);
        self::$data->setNested($type.".world-name", Server::getInstance()->getWorldManager()->getDefaultWorld()->getFolderName());
        self::$data->save();
    }

    /**
     * @param string $type
     * @return Position
     */
    public static function teleportTo(string $type): Position {
        return new Position(self::$data->getNested($type.".x"),self::$data->getNested($type.".y"),self::$data->getNested($type.".z"), Server::getInstance()->getWorldManager()->getWorldByName(self::$data->getNested($type.".world-name")) ?? Server::getInstance()->getWorldManager()->getDefaultWorld());
    }

    /**
     * @param Player $player
     * @param string $text
     * @return string
     */
    public static function formatter(Player $player, string $text): string {
        return (string)str_replace(["{PREFIX}", "{NAME}", "{WORLDNAME}", "{X}", "{Y}", "{Z}"], ["§l§b[§aHub§eSpawn§b] §r", $player->getName(), $player->getWorld()->getFolderName(), $player->getPosition()->getX(), $player->getPosition()->getY(), $player->getPosition()->getZ()], $text);
    }

}
