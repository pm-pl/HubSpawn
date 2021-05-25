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

namespace pedhot\hubspawn\command;

use CortexPE\Commando\BaseCommand;
use pedhot\hubspawn\HubSpawn;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class HubCommand extends BaseCommand
{

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        if (!$this->testPermission($sender)) return;
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED."Usage this command only in game!");
            return;
        }
        if (!$sender->hasPermission("hubspawn.hub")) {
            $sender->sendMessage(Server::getInstance()->getLanguage()->translateString(TextFormat::RED . "%commands.generic.permission"));
            return;
        }

        $sender->sendMessage(HubSpawn::formatter($sender, HubSpawn::getInstance()->getMessage()->getNested(HubSpawn::getInstance()->getSelectedLang().".hub-teleport")));
        $sender->teleport(HubSpawn::teleportTo("hub"));
    }

    protected function prepare(): void {
        $this->setAliases(HubSpawn::getInstance()->getConfig()->getNested("commands-settings.hub.aliases"));
        $this->setPermission("hubspawn.hub");
        $this->setDescription(HubSpawn::getInstance()->getConfig()->getNested("commands-settings.hub.description"));
        $this->setPermissionMessage(Server::getInstance()->getLanguage()->translateString(TextFormat::RED . "%commands.generic.permission"));
    }

}