<?php declare(strict_types = 1); namespace BapCat\CoolThing;

trait A {
    public function smallTalk(): string {
        return 'a';
    }
    public function bigTalk(): string {
        return 'A';
    }
}

trait B {
    public function smallTalk(): string {
        return 'b';
    }
    public function bigTalk(): string {
        return 'B';
    }
}

