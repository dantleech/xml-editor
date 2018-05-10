<?php

namespace Phpactor\XmlQuery;

interface SourceLoader
{
    public function loadSource(string $source): Node;
}
