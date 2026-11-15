import { NextResponse } from 'next/server';
import requestsData from '@/data/requests.json';

interface StrataRequest {
  id: number;
  title: string;
  requester: string;
  unit: string;
  status: string;
  date: string;
}

const seedRequests = requestsData.requests as StrataRequest[];

// GET handler to fetch all requests
export async function GET() {
  return NextResponse.json(seedRequests);
}

// POST handler to create a new request. The seed data ships with the build and
// Vercel's filesystem is read-only at runtime, so the request is validated and
// echoed back rather than saved. The client holds it for the session only.
export async function POST(request: Request) {
  try {
    const body = await request.json();

    for (const field of ['title', 'requester', 'unit'] as const) {
      if (!body[field]) {
        return NextResponse.json(
          { error: `Missing required field: ${field}` },
          { status: 400 },
        );
      }
    }

    const newRequest: StrataRequest = {
      id: Math.max(0, ...seedRequests.map((req) => req.id)) + 1,
      title: body.title,
      requester: body.requester,
      unit: body.unit,
      status: 'Pending',
      date: new Date().toISOString().split('T')[0],
    };

    return NextResponse.json(
      {
        ...newRequest,
        persisted: false,
        message:
          'Request accepted. This demo runs on read-only seed data, so it will not be saved.',
      },
      { status: 201 },
    );
  } catch (error) {
    return NextResponse.json(
      { error: 'Invalid JSON body' },
      { status: 400 },
    );
  }
}
