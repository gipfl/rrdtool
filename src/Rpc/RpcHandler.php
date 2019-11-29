<?php

namespace gipfl\RrdTool\Rpc;

use gipfl\Protocol\JsonRpc\Notification;
use gipfl\Protocol\JsonRpc\PacketHandler;
use gipfl\Protocol\JsonRpc\Request;
use gipfl\Protocol\JsonRpc\Response;
use gipfl\RrdTool\Graph\Shift;
use gipfl\RrdTool\GraphTemplate\TemplateLoader;
use gipfl\RrdTool\RrdGraph;
use gipfl\RrdTool\RrdGraphInfo;
use gipfl\RrdTool\RrdSummary;
use gipfl\RrdTool\Rrdtool;

class RpcHandler implements PacketHandler
{
    protected $rrdtool;

    public function __construct(Rrdtool $rrdtool)
    {
        $this->rrdtool = $rrdtool;
    }

    public function handle(Notification $packet)
    {
        if ($packet instanceof Request) {
            switch ($packet->getMethod()) {
                case 'rrd.version':
                    return 'v0.1.0';
                case 'rrd.graph':
                    return $this->prepareGraph($packet);
                case 'rrd.calculate':
                    return $this->calculate($packet);
            }
        }
        return Response::forRequest($packet)->setResult('nix');
    }

    protected function calculate(Request $packet)
    {
        $files = $packet->getParam('files');
        $dsNames = $packet->getParam('ds');
        $ds = [];
        foreach ($files as $file) {
            foreach ($dsNames as $name) {
                $ds[] = (object)[
                    'filename'   => $file,
                    'datasource' => $name,
                ];
            }
        }
        $start = $packet->getParam('start');
        $end = $packet->getParam('end');

        $summary = new RrdSummary($this->rrdtool);

        return $summary->summariesForDatasources($ds, $start, $end);
    }

    protected function prepareGraph(Request $packet)
    {
        $graph = new RrdGraph();
        $graph->setStart($packet->getParam('start'));
        $graph->setEnd($packet->getParam('end'));
        $graph->setFormat($packet->getParam('format'));
        $graph->setWidth($packet->getParam('width'));
        $graph->setHeight($packet->getParam('height'));
        $onlyGraph = $packet->getParam('onlyGraph');
        if ($onlyGraph) {
            $graph->setOnlyGraph();
        }

        if ($timeShift = $packet->getParam('timeShift')) {
            $graph->add(new Shift('timeShift', $timeShift));
        }

        $template = $packet->getParam('template');
        $file = $packet->getParam('file');

        $loader = new TemplateLoader();
        $template = $loader->load($template, $file);
        $template->setParams((array) $packet->getParams());
        $template->applyToGraph($graph);

        $info = new RrdGraphInfo($graph);

        return $info->getDetails($this->rrdtool);
    }
}
