<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoLti\models\classes\Importer;

use common_report_Report;
use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use Exception;
use oat\generis\model\OntologyRdf;
use oat\generis\model\OntologyRdfs;
use oat\oatbox\reporting\Report;
use oat\taoLti\models\classes\ConsumerService;
use tao_models_classes_import_RdfImporter;
use SimpleXMLElement;

class RdfImporter extends tao_models_classes_import_RdfImporter
{
    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function flatImport($content, core_kernel_classes_Class $class)
    {
        $parentReport = parent::flatImport($this->removeRootClassFromContent($content), $class);
        return $this->getMainReport($parentReport)->add($parentReport->getChildren());
    }

    /**
     * @inheritDoc
     */
    protected function importProperties(core_kernel_classes_Resource $resource, $propertiesValues, $map, $class)
    {
        if (isset($propertiesValues[OntologyRdfs::RDFS_SUBCLASSOF])) {
            return Report::createError(
                sprintf(
                    __('Importing subclasses on this resource is not allowed. Label: %s'),
                    $this->getLabelFromPropertiesValues($propertiesValues)
                )
            );
        }

        $types = $propertiesValues[OntologyRdf::RDF_TYPE] ?? [];
        if (count($types) === 1) {
            $resource->setType($class);
            unset($propertiesValues[OntologyRdf::RDF_TYPE]);
        }

        return parent::importProperties($resource, $propertiesValues, $map, $class);
    }

    private function getLabelFromPropertiesValues(array $propertiesValues): string
    {
        $labels = $propertiesValues[OntologyRdfs::RDFS_LABEL] ?? [];
        $firstLabel = reset($labels);

        return $firstLabel['value'] ?? '';
    }

    /**
     * @param string $content
     * @return string
     * @throws Exception
     */
    private function removeRootClassFromContent(string $content): string
    {
        $xml = new SimpleXMLElement($content);
        $rootClassNodes = $xml->xpath(sprintf('/rdf:RDF/rdf:Description[@rdf:about="%s"]', ConsumerService::CLASS_URI));
        foreach ($rootClassNodes as $rootClass) {
            $node = dom_import_simplexml($rootClass);
            $node->parentNode->removeChild($node);
        }

        return (string)$xml->asXML();
    }

    /**
     * @param common_report_Report $report
     * @return common_report_Report|Report
     * @throws \common_exception_Error
     */
    private function getMainReport(common_report_Report $report): common_report_Report
    {
        if ($report->contains(Report::TYPE_ERROR) && $report->contains(Report::TYPE_SUCCESS)) {
            return Report::createWarning(__("Some resources were not imported"));
        }

        if ($report->contains(Report::TYPE_ERROR)) {
            return Report::createError(__('Failed to import'));
        }

        return $report;
    }
}
