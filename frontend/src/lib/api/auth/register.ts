export async function registerRequest(
    name: string,
    email: string,
    password: string,
    password_confirmation: string
) {
    const res = await fetch('http://localhost:8000/api/v1/register', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json'
        },
        body: JSON.stringify({
            name,
            email,
            password,
            password_confirmation
        })
    });

    const data = await res.json();

    if (!res.ok) {
        throw new Error(data.message || 'Register gagal');
    }

    return data;
}