import http from 'k6/http';
import { sleep, check } from 'k6';


export const options = {
    vus: 10,
    duration: '30s',
    thresholds: {
        http_req_failed: ['rate<0.01'],
        http_req_duration: ['p(95)<250'],
    },
};

export default function() {

    http.get('http://localhost:8080');

    // const uniqueEmail = `user_${__VU}_${__ITER}@example.com`;
    //
    // let data = {
    //     first_name: 'ana',
    //     last_name: 'kelly',
    //     cpf: '459.780.150-23',
    //     phone: '(85) 9 8991-8135',
    //     email: uniqueEmail,
    //     password: 'Aurora@2024',
    //     confirm_password: 'Aurora@2024'
    // };
    //
    // http.get('http://localhost:8080/cadastro', JSON.stringify(data), {
    //     headers: {
    //         'Content-Type': 'application/json',
    //     },
    // });
    sleep(1);
}
