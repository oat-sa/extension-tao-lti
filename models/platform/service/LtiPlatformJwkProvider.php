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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA
 */

namespace oat\taoLti\models\platform\service;

use oat\oatbox\service\ConfigurableService;

class LtiPlatformJwkProvider extends ConfigurableService
{
    public function getPublicKey(): string
    {
        // @TODO Get key from proper place
        return '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAv8RBHIpqBmBQpedTCLaq
8xYStxVAQK/nzVh778CXPp0e6di6AayfT61YlttmX2nJ8sXjoQnUPaWNCfgzebLY
Hv9+hU+cmkR7FcY/y5/icNHefShSBykQaYJbpNU1610NV2wWpZ7ofy6TqSPsjG72
/ohFEGl0dtu9IV18mKbyTP3lhAlWDC73UQvQNinFNWsHFsagTaRISOdVaZzlbK7y
TDP0qggY8w5iHruPzrgOHhG9V6V4UFU2CwLJWiMiReLroThTk5H2K4P70Ro810nT
FsWHEs2reeP1U+cBL+C113ZkOEnWR97R7MhXOhaTMQ0cafHFDDFvcN410qCcZ7Pp
hwIDAQAB
-----END PUBLIC KEY-----';
    }

    public function getPrivateKey(): string
    {
        // @TODO Get key from proper place
        return '-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEAv8RBHIpqBmBQpedTCLaq8xYStxVAQK/nzVh778CXPp0e6di6
AayfT61YlttmX2nJ8sXjoQnUPaWNCfgzebLYHv9+hU+cmkR7FcY/y5/icNHefShS
BykQaYJbpNU1610NV2wWpZ7ofy6TqSPsjG72/ohFEGl0dtu9IV18mKbyTP3lhAlW
DC73UQvQNinFNWsHFsagTaRISOdVaZzlbK7yTDP0qggY8w5iHruPzrgOHhG9V6V4
UFU2CwLJWiMiReLroThTk5H2K4P70Ro810nTFsWHEs2reeP1U+cBL+C113ZkOEnW
R97R7MhXOhaTMQ0cafHFDDFvcN410qCcZ7PphwIDAQABAoIBAQCYaStyupOvw6b1
ZJf66euOTfHL7yjlAKmT7Ap8r63FRu1F4EldgUwQ8G3jYDbKCHNH732OBRjZchVZ
YhnPVJQudtOgsnh9p4XH9YvIk6dOEY7qHDytkjaIFOIvbIFxMcCjxbVuJpEUW/lh
ybp8lsjZ3YY+mAHHYbe8p+eaD72t8BDZnJ/xrDr83ZVZx0wyv85VHLoIXib8prO3
WSzc1vfv0iFfl6pB9HQG14EXbJUU7XBIFZTCt/c0OQSMWiQFuDoWmDAEbD8VKrgj
QefXv59VPxAaVmViTuJGBigycTFR1d7qnD2ZHuP0c7MNU6dHb4kRZ2G8IBo2B1av
xsUOv3VhAoGBAODCvnyrdtQ9KEKQqBkn7FVmo8C19jy26UiarnxbcyXKHdvSP2rB
s39NxRiuNqPLE5ejwL1PN/nSHA+MqFmXbn0NeTr5rbk/gsE5C4YmQOQs0je1Rc/X
u2IcqURXGZV3hHUE4m1+RtSV9Ik4gh4vPTZqugTT2ONLCc1niU4OGSR3AoGBANpr
imP66sRAAbg43EmQUESabpOBU4HGA0IKdeWsbQRuLkq646in1r2LpBuCcMVp4PEz
HXpoLhaySYj/D+xecJRjUu/TExf9WazTO2stL83vk3QzQW3wtNUSA5G9lkJdo5kF
eU7mL5kFOQsWfTi4livF/zCmWdfj01AyhhPt9PdxAoGBAL5YbxYJ6DLKMGRfOd1E
EEQrDpdQAt9cUKiqRwcOM7BXQX4+ynJQ5QD1Dexe7es9OrR7e+lXQ6KQrC8HAjQN
qQbC+F5XwSZXgRa+huHvV5ll1ApVvG/O1eS7PSahVQteEpE/t7re7IFxFY14i8mg
XAjPzgX0m4OysvR/sKqoCeb1AoGAWjcC8SrJXwfE7g9mRWg7DyruMzS+hiKAY/2o
FpYybRsJOcqZj6bLVnzf6pDk/VUvGNDxAwcWyj8XimM3c3zKaS/hKLd48natXGne
5/TBAwFKWTt2ce9y8XLAUKkK8Bx3HxQYt9Lxo/V5SzAdxpjPRgNA1RpQj5BFTqGf
qYmmnbECgYAc1Xzu96unWzkBsbtaEVg2ZGUOwMpprKfc39hvQRLeTJNMUqKV2ReI
KvQ1n0Bm3VpliUwlgt5ovZC3aO+8B0hJBI+IYdSgfRpdG36Pu6OT4yeW2rJljGOj
0WaT2YCPymPtgZbNoqd18hDk/8KYyvYAqflArPep2G8qSShyem2Xnw==
-----END RSA PRIVATE KEY-----';
    }
}
