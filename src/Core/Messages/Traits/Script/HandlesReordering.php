<?php

namespace Cognesy\Instructor\Core\Messages\Traits\Script;

use Cognesy\Instructor\Core\Messages\Script;
use Cognesy\Instructor\Core\Messages\Section;

trait HandlesReordering
{
    public function reorder(array $order) : Script {
        $sections = $this->listInOrder($order);

        $script = new Script();
        $script->context = $this->context;
        foreach ($sections as $section) {
            $script->append($section);
        }
        return $script;
    }

    public function reverse() : Script {
        $script = new Script();
        $script->context = $this->context;
        foreach ($this->listReverse() as $section) {
            $script->append($section);
        }
        return $script;
    }

    // INTERNAL ////////////////////////////////////////////////////

    /** @return Section[] */
    private function listAsIs() : array {
        return $this->sections;
    }

    /** @return Section[] */
    private function listReverse() : array {
        return array_reverse($this->sections);
    }

    /** @return Section[] */
    private function listInOrder(array $order) : array {
        $ordered = [];
        foreach ($order as $name) {
            if (!$this->hasSection($name)) {
                continue;
            }
            $section = $this->section($name);
            $ordered[] = $section;
        }
        return $ordered;
    }
}