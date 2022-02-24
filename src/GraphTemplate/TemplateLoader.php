<?php

namespace gipfl\RrdTool\GraphTemplate;

class TemplateLoader
{
    public function load($template, $filename)
    {
        switch ($template) {
            case 'interface':
                return new InterfaceGraph($filename);
            case 'ointerface':
                return new OctetsInterfaceGraph($filename);
            case 'load':
                return new LoadGraph($filename);
            case 'ido':
                return new IdoGraph($filename);
            case 'pnp_interfaces':
                return new PnpInterfaceGraph($filename);
            case 'RRDHealth':
                return new RRDHealthGraph($filename);
            case 'RRDCacheDUpdates':
                return new RRDCacheDUpdatesGraph($filename);
            case 'vSphereDB-vmIfTraffic':
                return new VmwareInterfaceGraph($filename);
            case 'vSphereDB-vmIfPackets':
                return new VmwareIfPacketsGraph($filename);
            case 'vSphereDB-vmDiskSeeks':
                return new VmwareDiskSeeksGraph($filename);
            case 'vSphereDB-vmDiskReadWrites':
                return new VmwareDiskReadWritesGraph($filename);
            case 'vSphereDB-vmDiskTotalLatency':
                return new VmwareDiskTotalLatencyGraph($filename);
            case 'vm_disk':
                return new VmwareDiskUsageGraph($filename);
            case 'cpu_metrics':
                return new CpuMetricsGraph($filename);
            case 'cpu':
                return new CpuGraph($filename);
            default:
                return new DefaultGraph($filename);
        }
    }
}
