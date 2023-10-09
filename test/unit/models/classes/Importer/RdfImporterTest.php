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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace oat\taoLti\test\unit\models\classes\Importer;

use core_kernel_classes_Class;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use core_kernel_persistence_ResourceInterface;
use core_kernel_persistence_smoothsql_SmoothModel;
use oat\generis\model\data\Ontology;
use oat\generis\model\data\RdfsInterface;
use oat\generis\model\kernel\uri\UriProvider;
use oat\generis\model\OntologyRdfs;
use oat\oatbox\event\EventAggregator;
use oat\oatbox\service\ServiceManager;
use oat\taoLti\models\classes\Importer\RdfImporter;
use oat\taoLti\test\unit\OntologyMockTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionMethod;

class RdfImporterTest extends TestCase
{
    use OntologyMockTrait;

    private RdfImporter $sut;
    private core_kernel_classes_Class|MockObject $resourceClassMock;

    private core_kernel_persistence_ResourceInterface|MockObject $resourceMock;
    private ServiceManager $currentServiceManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new RdfImporter();

        $uriProvider = $this->createMock(UriProvider::class);
        $uriProvider->expects(self::any())
            ->method('provide')
            ->willReturn('https://tao.loocal/#123');

        $ontologyMock = $this->buildOntologyMock();

        $this->currentServiceManager = ServiceManager::getServiceManager();

        $serviceManager = $this->getServiceManagerMock([
            Ontology::SERVICE_ID => $ontologyMock,
            UriProvider::SERVICE_ID => $uriProvider,
            EventAggregator::SERVICE_ID => $this->createMock(EventAggregator::class),
        ]);
        ServiceManager::setServiceManager($serviceManager);

        $ontologyMock->expects(self::any())
            ->method('getServiceLocator')
            ->willReturn($serviceManager);

        $this->resourceClassMock = $this->createMock(core_kernel_classes_Class::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        ServiceManager::setServiceManager($this->currentServiceManager);
    }

    /**
     * @throws ReflectionException
     */
    public function testFlatImportSucceeded(): void
    {
        /** @noinspection HttpUrlsUsage */
        $content = <<<'CONTENT'
<?xml version="1.0" encoding="utf-8" ?>
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
         xmlns:ns0="http://www.tao.lu/Ontologies/TAO.rdf#"
         xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
         xmlns:ns1="c"
         xmlns:ns2="r">

  <rdf:Description rdf:about="http://www.tao.lu/Ontologies/TAOLTI.rdf#LTIConsumer">
    <ns0:UpdatedAt>1696259911.1005</ns0:UpdatedAt>
    <rdfs:label xml:lang="en-US">LTI Consumer</rdfs:label>
    <rdfs:subClassOf>http://www.tao.lu/Ontologies/TAO.rdf#OauthConsumer</rdfs:subClassOf>
  </rdf:Description>

  <rdf:Description rdf:about="https://install.docker.localhost/ontologies/tao.rdf#i651adf8063a0195b2138c3553abfda">
    <ns1:lassUri>http://www.tao.lu/Ontologies/TAOLTI.rdf#LTIConsumer</ns1:lassUri>
    <ns0:OauthKey>devel</ns0:OauthKey>
    <ns0:OauthSecret>b651639c2473501be299b461882f1db89484fe4f</ns0:OauthSecret>
    <ns0:UpdatedAt>1696259968.4526</ns0:UpdatedAt>
    <rdf:type>http://www.tao.lu/Ontologies/TAOLTI.rdf#LTIConsumer</rdf:type>
    <rdfs:label xml:lang="en-US">consumer</rdfs:label>
  </rdf:Description>
</rdf:RDF>
CONTENT;

        $this->resourceMock->expects(self::once())
            ->method('setType')
            ->with(new core_kernel_classes_Resource('https://tao.loocal/#123'), $this->resourceClassMock);

        $flatImportMethod = new ReflectionMethod(RdfImporter::class, 'flatImport');
        $flatImportMethod->setAccessible(true);
        $report = $flatImportMethod->invoke($this->sut, $content, $this->resourceClassMock);

        self::assertEquals('success', $report->getType());

        self::assertEquals('Data imported successfully', (string)$report);
    }

    /**
     * @throws ReflectionException
     */
    public function testFlatImportFailed(): void
    {
        /** @noinspection HttpUrlsUsage */
        $content = <<<'CONTENT'
<?xml version="1.0" encoding="utf-8" ?>
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
         xmlns:ns0="http://www.tao.lu/Ontologies/TAO.rdf#"
         xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
         xmlns:ns1="c"
         xmlns:ns2="r">

  <rdf:Description rdf:about="https://qa-server.eu.premium.lab.taocloud.org/#i64f70d9b1480524078080821f726a7350">
    <ns0:UpdatedAt>1693912475.4922</ns0:UpdatedAt>
    <rdfs:label xml:lang="en-US">LTI Consumer</rdfs:label>
    <rdfs:subClassOf>http://www.tao.lu/Ontologies/TAOLTI.rdf#LTIConsumer</rdfs:subClassOf>
  </rdf:Description>
</rdf:RDF>
CONTENT;

        $this->resourceMock->expects(self::never())
            ->method('setType');

        $flatImportMethod = new ReflectionMethod(RdfImporter::class, 'flatImport');
        $flatImportMethod->setAccessible(true);
        $report = $flatImportMethod->invoke($this->sut, $content, $this->resourceClassMock);

        self::assertEquals('warning', $report->getType());

        self::assertEquals('Some imports were not possible', (string)$report);
        self::assertStringContainsString(
            'Importing subclasses on this resource is not allowed. Label: LTI Consumer',
            json_encode($report)
        );
        self::assertStringNotContainsString(
            'Successfully imported',
            json_encode($report)
        );
    }


    /**
     * @throws ReflectionException
     */
    public function testFlatImportPartiallyImported(): void
    {
        /** @noinspection HttpUrlsUsage */
        $content = <<<'CONTENT'
<?xml version="1.0" encoding="utf-8" ?>
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
         xmlns:ns0="http://www.tao.lu/Ontologies/TAO.rdf#"
         xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
         xmlns:ns1="c"
         xmlns:ns2="r">

  <rdf:Description rdf:about="https://install.docker.localhost/ontologies/tao.rdf#i651adf8063a0195b2138c3553abfda">
    <ns1:lassUri>http://www.tao.lu/Ontologies/TAOLTI.rdf#LTIConsumer</ns1:lassUri>
    <ns0:OauthKey>devel</ns0:OauthKey>
    <ns0:OauthSecret>b651639c2473501be299b461882f1db89484fe4f</ns0:OauthSecret>
    <ns0:UpdatedAt>1696259968.4526</ns0:UpdatedAt>
    <rdf:type>http://www.tao.lu/Ontologies/TAOLTI.rdf#LTIConsumer</rdf:type>
    <rdfs:label xml:lang="en-US">consumer</rdfs:label>
  </rdf:Description>
  
  <rdf:Description rdf:about="https://qa-server.eu.premium.lab.taocloud.org/#i64f70d9b1480524078080821f726a7350">
    <ns0:UpdatedAt>1693912475.4922</ns0:UpdatedAt>
    <rdfs:label xml:lang="en-US">LTI Consumer</rdfs:label>
    <rdfs:subClassOf>http://www.tao.lu/Ontologies/TAOLTI.rdf#LTIConsumer</rdfs:subClassOf>
  </rdf:Description>
</rdf:RDF>
CONTENT;

        $this->resourceMock->expects(self::once())
            ->method('setType');

        $flatImportMethod = new ReflectionMethod(RdfImporter::class, 'flatImport');
        $flatImportMethod->setAccessible(true);
        $report = $flatImportMethod->invoke($this->sut, $content, $this->resourceClassMock);

        self::assertEquals('warning', $report->getType());

        self::assertEquals('Some imports were not possible', (string)$report);
        self::assertStringContainsString(
            'Importing subclasses on this resource is not allowed. Label: LTI Consumer',
            json_encode($report)
        );
        self::assertStringContainsString(
            'Successfully imported',
            json_encode($report)
        );
    }

    private function buildOntologyMock(): MockObject
    {
        $ontologyMock = $this->createMock(core_kernel_persistence_smoothsql_SmoothModel::class);
        $rdfsMock = $this->createMock(RdfsInterface::class);
        $this->resourceMock = $this->createMock(core_kernel_persistence_ResourceInterface::class);

        $rdfsMock->expects(self::any())
            ->method('getResourceImplementation')
            ->willReturn($this->resourceMock);
        $ontologyMock->expects(self::any())
            ->method('getRdfsInterface')
            ->willReturn($rdfsMock);
        $ontologyMock->expects(self::any())
            ->method('getProperty')
            ->with(OntologyRdfs::RDFS_LABEL)
            ->willReturn(new core_kernel_classes_Property('label'));

        return $ontologyMock;
    }
}
