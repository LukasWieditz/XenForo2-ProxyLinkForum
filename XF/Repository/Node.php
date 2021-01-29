<?php

namespace SV\ProxyLinkForum\XF\Repository;


/**
 * Class Node
 * @package SV\ProxyLinkForum\XF\Repository
 */
class Node extends XFCP_Node
{
    /**
     * @var
     */
    protected $fullNodeTreeExtras;

    /**
     * @param $node
     * @param $extras
     * @return mixed
     */
    protected function getSvPLFProxiedNodeData($node, $extras) {
        $nodeId = $node->node_id;

        if(isset($extras[$nodeId])) {
            return $extras[$nodeId];
        }

        if(!$this->fullNodeTreeExtras) {
            $this->fullNodeTreeExtras = $this->getNodeListExtras($this->createNodeTree($this->getNodeList(), 0));
        }

        return $this->fullNodeTreeExtras[$nodeId];
    }

    /**
     * @param \XF\Tree $nodeTree
     * @return array
     */
    public function getNodeListExtras(\XF\Tree $nodeTree)
    {
        $finalOutput = parent::getNodeListExtras($nodeTree);

        $f = function(\XF\Entity\Node $node, array $children) use (&$f, &$finalOutput)
        {
            foreach ($children AS $id => $child)
            {
                /** @var \XF\SubTree $child */
                $childOutput[$id] = $f($child->record, $child->children());
            }

            $extras = $node->getNodeListExtras();
            $proxy = $extras['ProxiedNode'] ?? null;
            if($proxy) {
                $finalOutput[$node->node_id] = $this->mergeNodeListExtras($finalOutput[$node->node_id], [$this->getSvPLFProxiedNodeData($proxy->Node, $finalOutput)]);
            }
        };

        foreach ($nodeTree AS $id => $subTree)
        {
            $f($subTree->record, $subTree->children());
        }

        return $finalOutput;
    }
}
