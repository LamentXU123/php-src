--TEST--
SOAP XML Schema 87: maxOccurs integer overflow
--EXTENSIONS--
soap
--INI--
soap.wsdl_cache_enabled=0
--FILE--
<?php
$wsdl = <<<XML
<?xml version="1.0"?>
<definitions name="OverflowTest"
    xmlns:xsd="http://www.w3.org/2001/XMLSchema"
    xmlns:tns="http://test-uri/"
    xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/"
    xmlns="http://schemas.xmlsoap.org/wsdl/"
    targetNamespace="http://test-uri/">
  <types>
    <xsd:schema targetNamespace="http://test-uri/">
      <xsd:complexType name="testType">
        <xsd:sequence>
          <xsd:element name="item" type="xsd:string" maxOccurs="2147483648"/>
        </xsd:sequence>
      </xsd:complexType>
    </xsd:schema>
  </types>
  <message name="testMessage">
    <part name="testParam" type="tns:testType"/>
  </message>
  <portType name="testPortType">
    <operation name="test">
      <input message="tns:testMessage"/>
    </operation>
  </portType>
  <binding name="testBinding" type="tns:testPortType">
    <soap:binding style="rpc" transport="http://schemas.xmlsoap.org/soap/http"/>
    <operation name="test">
      <soap:operation soapAction="#test"/>
      <input>
        <soap:body use="encoded" namespace="http://test-uri/" encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"/>
      </input>
    </operation>
  </binding>
  <service name="testService">
    <port name="testPort" binding="tns:testBinding">
      <soap:address location="test://"/>
    </port>
  </service>
</definitions>
XML;

$file = tempnam(__DIR__, "wsdl");
file_put_contents($file, $wsdl);

try {
    new SoapClient($file);
} catch (SoapFault $e) {
    echo $e->faultstring, "\n";
}

unlink($file);
?>
--EXPECT--
SOAP-ERROR: Parsing Schema: maxOccurs value is out of range
